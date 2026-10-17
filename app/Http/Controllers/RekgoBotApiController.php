<?php
namespace App\Http\Controllers;
use App\Models\{User,Escrow};
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class RekgoBotApiController {
 private function registeredUser(string $phone):User {
  $digits=preg_replace('/\\D+/','',$phone);
  $local=str_starts_with($digits,'62')?'0'.substr($digits,2):$digits;
  $international=str_starts_with($local,'0')?'62'.substr($local,1):$local;
  $user=User::whereIn('phone',array_unique([$local,$international,'+'.$international]))->first();
  abort_unless($user && !$user->suspended_at,403,'Nomor WhatsApp belum terdaftar di RekGo. Daftar terlebih dahulu di https://rekgo.my.id/login#daftar');
  return $user;
 }
 private function authorizeBot(Request $r):void {
  $key=(string)config('services.rekgo_bot.key','');
  abort_unless(strlen($key)>=32 && hash_equals($key,(string)$r->bearerToken()),401);
 }
 public function account(Request $r){
  $this->authorizeBot($r);
  $d=$r->validate(['phone'=>'required|regex:/^[0-9]{9,16}$/']);
  $u=$this->registeredUser($d['phone']);
  $b=\App\Models\Balance::firstOrCreate(['user_id'=>$u->id]);
  $wd=\App\Models\Withdrawal::where('user_id',$u->id);
  $es=Escrow::where('seller_id',$u->id);
  $pay=\App\Models\Payment::where('user_id',$u->id);
  return response()->json(['name'=>$u->name,'username'=>$u->username,'balance'=>['available'=>(int)$b->available,'pending'=>(int)$b->pending,'locked'=>(int)$b->locked,'total'=>(int)$b->available+(int)$b->pending+(int)$b->locked],'bank'=>['name'=>$u->bank_code?:'-','account_masked'=>$u->bank_account?'****'.substr($u->bank_account,-4):'-','holder'=>$u->bank_name?:'-','verified'=>(bool)$u->bank_verified_at],'transactions'=>['total'=>(clone $es)->count()+(clone $pay)->count(),'escrow'=>(clone $es)->count(),'payments'=>(clone $pay)->count()],'withdrawals'=>['total'=>(clone $wd)->count(),'pending'=>(clone $wd)->whereIn('status',['pending','processing'])->count(),'completed'=>(clone $wd)->whereIn('status',['completed','success','paid'])->count(),'total_paid'=>(int)(clone $wd)->whereIn('status',['completed','success','paid'])->sum('amount')]]);
 }
 public function create(Request $r,Wallet $wallet){
  $this->authorizeBot($r);
  $d=$r->validate(['request_id'=>'required|string|max:100','group_jid'=>'required|string|max:150','seller_jid'=>'required|string|max:150','seller_phone'=>'required|regex:/^[0-9]{9,16}$/','amount'=>'required|integer|min:1000|max:1000000000']);
  $result=DB::transaction(function()use($d,$wallet){
   $existing=DB::table('rekgo_bot_requests')->where('request_id',$d['request_id'])->first();
   if($existing){abort_unless($existing->group_jid===$d['group_jid'] && $existing->seller_jid===$d['seller_jid'],409,'Request ID conflict');return Escrow::findOrFail($existing->escrow_id);}
   $user=$this->registeredUser($d['seller_phone']);
   $fee=$wallet->fee('escrow',$d['amount']);
   abort_if($fee<=0,422,'Tarif rekber untuk nominal ini belum dikonfigurasi.');
   $escrow=Escrow::create(['seller_id'=>$user->id,'title'=>'RekGo WhatsApp · '.$d['request_id'],'amount'=>$d['amount'],'fee'=>$fee,'fee_payer'=>$user->fee_payer,'status'=>'pending','expires_at'=>now()->addDay()]);
   DB::table('rekgo_bot_requests')->insert(['request_id'=>$d['request_id'],'group_jid'=>$d['group_jid'],'seller_jid'=>$d['seller_jid'],'seller_id'=>$user->id,'escrow_id'=>$escrow->id,'created_at'=>now(),'updated_at'=>now()]);
   $wallet->audit($user->id,'bot.escrow_created','escrow',$escrow->id,['group_jid'=>$d['group_jid']]);
   return $escrow;
  },3);
  return response()->json(['id'=>$result->id,'amount'=>$result->amount,'fee'=>$result->fee,'fee_payer'=>$result->fee_payer,'status'=>$result->status,'message'=>'Transaksi dibuat. Belum ada instruksi pembayaran sampai metode pembayaran terverifikasi.']);
 }
 public function done(Request $r,Wallet $wallet){
  $this->authorizeBot($r);
  $d=$r->validate(['group_jid'=>'required|string|max:150','buyer_jid'=>'required|string|max:150','buyer_phone'=>'required|regex:/^[0-9]{9,16}$/']);
  $record=DB::table('rekgo_bot_requests')->where('group_jid',$d['group_jid'])->orderByDesc('id')->first();
  abort_unless($record,404,'Tidak ada transaksi di grup ini.');
  abort_if($d['buyer_jid']===$record->seller_jid,403,'Seller tidak boleh mengonfirmasi transaksi sendiri.');
  $seller=User::findOrFail($record->seller_id);
  $sellerPhone=preg_replace('/\\D+/','',(string)$seller->phone);
  $buyerPhone=preg_replace('/\\D+/','',$d['buyer_phone']);
  $normalize=static fn(string $number):string => str_starts_with($number,'0')?'62'.substr($number,1):$number;
  abort_if($normalize($buyerPhone)===$normalize($sellerPhone),403,'Seller tidak boleh mengonfirmasi transaksi sendiri.');
  $escrow=Escrow::findOrFail($record->escrow_id);
  abort_unless($escrow->status==='paid',409,'Pembayaran belum terverifikasi atau status transaksi tidak sesuai.');
  $wallet->audit(null,'bot.buyer_done_requested','escrow',$escrow->id,['buyer_jid'=>$d['buyer_jid'],'buyer_phone_suffix'=>substr($buyerPhone,-4)]);
  return response()->json(['id'=>$escrow->id,'status'=>$escrow->status,'message'=>'Permintaan selesai dicatat untuk pemeriksaan admin. Dana belum dicairkan.']);
 }
}