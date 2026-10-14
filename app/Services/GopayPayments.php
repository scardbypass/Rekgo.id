<?php
namespace App\Services;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
class GopayPayments {
 public function __construct(private GopayBridge $bridge,private Wallet $wallet){}
 private function data(array $r):array {return is_array($r['data']??null)?$r['data']:$r;}
 private function reference(array $r):?string {$d=$this->data($r);foreach(['transaction_id','transactionId','id'] as $k){if(isset($d[$k])&&is_scalar($d[$k]))return (string)$d[$k];}return null;}
 private function qr(array $r):?string {$d=$this->data($r);foreach(['qr_string','qrString','qr_code','qrCode','qr_payload'] as $k){if(isset($d[$k])&&is_string($d[$k]))return $d[$k];}return null;}
 public function create(Payment $payment):void {
  if($payment->status!=='pending'||$payment->provider_ref)throw new RuntimeException('Payment is not available for creation');
  $result=$this->bridge->call('create',['amount'=>(int)$payment->gross,'label'=>'RekGo-'.$payment->id]);
  $ref=$this->reference($result);$qr=$this->qr($result);
  // Never publish a charge without a verifiable provider reference AND QR payload.
  if(!$ref||!preg_match('/^[\w.-]{1,150}$/',$ref)||!$qr){Log::warning('GoPay response lacks reference or QR payload',['payment_id'=>$payment->id,'keys'=>array_keys($this->data($result))]);throw new RuntimeException('Provider response not mapped: reference/QR missing');}
  DB::transaction(function()use($payment,$ref,$qr){$locked=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();if($locked->provider_ref)return;$locked->update(['provider'=>'gopay_marc','provider_ref'=>$ref,'qr_payload'=>$qr,'next_check_at'=>now()->addMinute()]);$this->wallet->audit($locked->user_id,'qris.provider_created','payment',$locked->id);});
 }
 public function check(Payment $payment):void {
  if($payment->status!=='pending'||!$payment->provider_ref)return;
  $result=$this->bridge->call('status',['reference'=>$payment->provider_ref]);$d=$this->data($result);
  $status=strtolower((string)($d['transaction_status']??$d['status']??''));
  // Unknown or ambiguous statuses must never credit a wallet.
  $success=in_array($status,['settlement','capture','paid','success'],true);
  $providerAmount=$d['gross_amount']??$d['grossAmount']??$d['amount']??null;
  $providerRef=$this->reference($result);
  $verified=$success && $providerRef!==null && hash_equals($payment->provider_ref,$providerRef) && ExactIdrAmount::matches($providerAmount,(int)$payment->gross);
  DB::transaction(function()use($payment,$status,$verified){$locked=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();if($locked->status!=='pending')return;
   $locked->provider_status=substr($status,0,60);$locked->last_checked_at=now();$locked->check_attempts++;$locked->next_check_at=now()->addMinute();
   if($verified){$balance=$this->wallet->balance($locked->user_id);$this->wallet->move($balance,'available',(int)$locked->net,'payment',$locked->id,'QRIS provider confirmed');$locked->status='success';$locked->paid_at=now();$locked->next_check_at=null;$this->wallet->audit(null,'qris.confirmed','payment',$locked->id,['provider_status'=>$status,'gross'=>$locked->gross,'fee'=>$locked->fee]);}
   elseif($status==='success'||$status==='paid'||$status==='settlement'||$status==='capture'){$locked->next_check_at=null;Log::critical('QRIS success with unverified amount or reference; manual reconciliation required',['payment_id'=>$locked->id]);}
   $locked->save();
  });
 }

 // Fail-closed expiry: never expire a payment while provider state is unknown.
 // Check the provider first, then request cancellation, then check once more.
 public function reconcileExpiry(Payment $payment):void {
  if($payment->status!=='pending'||!$payment->provider_ref||!$payment->expires_at||$payment->expires_at->isFuture())return;
  $this->check($payment);
  $payment->refresh();if($payment->status!=='pending')return;
  try{$this->bridge->call('cancel',['reference'=>$payment->provider_ref]);}
  catch(\Throwable $e){Log::warning('QRIS cancel needs retry',['payment_id'=>$payment->id]);$this->retry($payment);return;}
  // Do not trust cancellation acknowledgement alone; provider must explicitly confirm terminal cancellation.
  try{$result=$this->bridge->call('status',['reference'=>$payment->provider_ref]);}
  catch(\Throwable $e){$this->retry($payment);return;}
  $d=$this->data($result);$status=strtolower((string)($d['transaction_status']??$d['status']??''));
  $ref=$this->reference($result);
  if(in_array($status,['settlement','capture','paid','success'],true)){$this->check($payment);return;}
  if(!$ref||!hash_equals($payment->provider_ref,$ref)||!in_array($status,['cancelled','canceled','expired'],true)){$this->retry($payment);return;}
  DB::transaction(function()use($payment,$status){$p=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();if($p->status!=='pending')return;$p->update(['status'=>'expired','provider_status'=>$status,'next_check_at'=>null,'qr_payload'=>null]);$this->wallet->audit(null,'qris.expired','payment',$p->id,['provider_status'=>$status]);});
 }
 private function retry(Payment $payment):void {Payment::whereKey($payment->id)->where('status','pending')->update(['next_check_at'=>now()->addMinute()]);}
}

