<?php
namespace App\Http\Controllers;
use App\Models\{User,Escrow};
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class RekgoBotApiController {
 private function authorizeBot(Request $r):void {
  $key=(string)config('services.rekgo_bot.key','');
  abort_unless(strlen($key)>=32 && hash_equals($key,(string)$r->bearerToken()),401);
 }
 public function create(Request $r,Wallet $wallet){
  $this->authorizeBot($r);
  $d=$r->validate(['request_id'=>'required|string|max:100','group_jid'=>'required|string|max:150','seller_jid'=>'required|string|max:150','seller_phone'=>'required|regex:/^[0-9]{9,16}$/','amount'=>'required|integer|min:1000|max:1000000000']);
  $result=DB::transaction(function()use($d,$wallet){
   $existing=DB::table('rekgo_bot_requests')->where('request_id',$d['request_id'])->first();
   if($existing){abort_unless($existing->group_jid===$d['group_jid'] && $existing->seller_jid===$d['seller_jid'],409,'Request ID conflict');return Escrow::findOrFail($existing->escrow_id);}
   $phone=preg_replace('/^62/','0',$d['seller_phone']);
   $user=User::whereIn('phone',array_unique([$d['seller_phone'],'+'.$d['seller_phone'],$phone]))->first();
   abort_unless($user && !$user->suspended_at,422,'Nomor WhatsApp seller belum terdaftar atau akun nonaktif.');
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
  $d=$r->validate(['group_jid'=>'required|string|max:150','buyer_jid'=>'required|string|max:150']);
  $record=DB::table('rekgo_bot_requests')->where('group_jid',$d['group_jid'])->orderByDesc('id')->first();
  abort_unless($record,404,'Tidak ada transaksi di grup ini.');
  $escrow=Escrow::findOrFail($record->escrow_id);
  abort_unless($escrow->status==='paid',409,'Pembayaran belum terverifikasi atau status transaksi tidak sesuai.');
  $wallet->audit(null,'bot.buyer_done_requested','escrow',$escrow->id,['buyer_jid'=>$d['buyer_jid']]);
  return response()->json(['id'=>$escrow->id,'status'=>$escrow->status,'message'=>'Permintaan selesai dicatat untuk pemeriksaan admin. Dana belum dicairkan.']);
 }
}