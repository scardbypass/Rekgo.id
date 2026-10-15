<?php
namespace App\Http\Controllers;
use App\Models\{Balance,Withdrawal,Escrow,Payment};
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class UserController {
 public function dashboard(Request $r){$u=$r->user();$b=Balance::firstOrCreate(['user_id'=>$u->id]);return view('dashboard',['u'=>$u,'b'=>$b,'withdrawals'=>Withdrawal::where('user_id',$u->id)->latest()->limit(10)->get(),'escrows'=>Escrow::where('seller_id',$u->id)->latest()->limit(10)->get(),'payments'=>Payment::where('user_id',$u->id)->latest()->limit(10)->get()]);}
 public function account(Request $r){return view('account',['user'=>$r->user(),'balance'=>Balance::firstOrCreate(['user_id'=>$r->user()->id])]);}
 public function transfer(Request $r){return view('transfer',['user'=>$r->user(),'balance'=>Balance::firstOrCreate(['user_id'=>$r->user()->id]),'withdrawals'=>Withdrawal::where('user_id',$r->user()->id)->latest()->limit(30)->get()]);}
 public function history(Request $r){$id=$r->user()->id;return view('history',['payments'=>Payment::where('user_id',$id)->latest()->limit(50)->get(),'withdrawals'=>Withdrawal::where('user_id',$id)->latest()->limit(50)->get(),'escrows'=>Escrow::where('seller_id',$id)->latest()->limit(50)->get()]);}
 public function rekber(Request $r){return redirect('/history');}
 public function qrisPage(Request $r){$pg=DB::table('payment_provider_settings')->where('provider','gopay_marc')->first();return view('qris',['enabled'=>(bool)($pg?->enabled && config('rekgo.gopay_live_verified')===true),'payments'=>Payment::where('user_id',$r->user()->id)->latest()->limit(15)->get()]);}
 public function settings(Request $r,Wallet $w){$d=$r->validate(['fee_payer'=>'required|in:buyer,seller']);$r->user()->update($d);$w->audit($r->user()->id,'fee.preference','user',$r->user()->id,$d);return back()->with('ok','Preferensi fee diperbarui.');}
 public function avatar(Request $r,Wallet $w){$r->validate(['avatar'=>'required|image|mimes:jpeg,png,webp|max:2048']);$u=$r->user();$path=$r->file('avatar')->store('avatars','public');$old=$u->avatar_path;$u->update(['avatar_path'=>$path]);if($old)Storage::disk('public')->delete($old);$w->audit($u->id,'avatar.updated','user',$u->id);return back()->with('ok','Foto profil disimpan.');}
 public function bank(Request $r,Wallet $w){$d=$r->validate(['bank_code'=>'required|in:BCA,BRI,BNI,MANDIRI','bank_account'=>'required|digits_between:8,20']);$r->user()->update($d+['bank_name'=>null,'bank_verified_at'=>null]);$w->audit($r->user()->id,'bank.pending_verification','user',$r->user()->id,['bank_code'=>$d['bank_code']]);return back()->with('ok','Rekening tersimpan, belum terverifikasi. WD baru dinonaktifkan sampai inquiry bank berhasil.');}
 public function withdraw(Request $r,Wallet $w){$d=$r->validate(['amount'=>'required|integer|min:10000|max:1000000000']);$u=$r->user();abort_unless($u->bank_verified_at&&$u->bank_name,422,'Rekening belum terverifikasi oleh provider bank.');DB::transaction(function()use($w,$u,$d){$b=$w->balance($u->id);$wd=Withdrawal::create(['user_id'=>$u->id,'amount'=>$d['amount'],'bank_code'=>$u->bank_code,'bank_account'=>$u->bank_account,'bank_name'=>$u->bank_name,'verified_at'=>$u->bank_verified_at]);$w->move($b,'available',-$d['amount'],'withdrawal',$wd->id,'WD diajukan');$w->move($b,'locked',$d['amount'],'withdrawal',$wd->id,'WD terkunci');$w->audit($u->id,'withdrawal.created','withdrawal',$wd->id);});return back()->with('ok','Pengajuan WD dibuat.');}
 public function escrow(Request $r,Wallet $w){$d=$r->validate(['title'=>'required|string|max:150','amount'=>'required|integer|min:1000|max:1000000000']);$u=$r->user();$e=Escrow::create(['seller_id'=>$u->id,'title'=>$d['title'],'amount'=>$d['amount'],'fee'=>$w->fee('escrow',$d['amount']),'fee_payer'=>$u->fee_payer,'status'=>'pending','expires_at'=>now()->addDay()]);$w->audit($u->id,'escrow.created','escrow',$e->id);return back()->with('ok','TRX rekber dibuat; menunggu pembayaran terverifikasi.');}
 public function qris(Request $r,Wallet $w,\App\Services\GopayPayments $gateway){
  $d=$r->validate(['amount'=>'required|integer|min:1000|max:1000000000','fee_payer'=>'required|in:sender,receiver']);
  abort_unless(config('rekgo.gopay_live_verified') === true,503,'QRIS live belum diverifikasi untuk lingkungan ini.');
  $pg=DB::table('payment_provider_settings')->where('provider','gopay_marc')->first();abort_unless($pg&&$pg->enabled,503,'QRIS belum diaktifkan oleh Admin.');
  // MDR QRIS may not be passed to consumers; sender option disabled until a compliant fee model is approved.
  abort_if($d['fee_payer']==='sender',422,'Fee QRIS kepada pembayar belum diizinkan.');
  $fee=$w->fee('qris',$d['amount']);$net=$d['amount']-$fee;abort_if($net<=0,422,'Nominal tidak mencukupi untuk fee.');
  $payment=Payment::create(['user_id'=>$r->user()->id,'amount'=>$d['amount'],'fee'=>$fee,'fee_payer'=>'receiver','gross'=>$d['amount'],'net'=>$net,'status'=>'pending','provider'=>'gopay_marc','expires_at'=>now()->addMinutes($pg->expiry_minutes)]);
  try{$gateway->create($payment);}catch(\Throwable $e){\Illuminate\Support\Facades\Log::error('QRIS creation failed',['payment_id'=>$payment->id,'error'=>$e->getMessage()]);return back()->withErrors(['qris'=>'QRIS belum dapat dibuat. Jangan melakukan pembayaran sampai QR muncul.']);}
  return redirect()->route('qris.show',$payment);
 }
}
