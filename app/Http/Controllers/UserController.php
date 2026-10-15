<?php
namespace App\Http\Controllers;
use App\Models\{Balance,Withdrawal,Escrow,Payment};
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class UserController {
 public function dashboard(Request $r){$u=$r->user();$b=Balance::firstOrCreate(['user_id'=>$u->id]);return view('dashboard',['u'=>$u,'b'=>$b,'withdrawals'=>Withdrawal::where('user_id',$u->id)->latest()->limit(10)->get(),'escrows'=>Escrow::where('seller_id',$u->id)->latest()->limit(10)->get(),'payments'=>Payment::where('user_id',$u->id)->latest()->limit(10)->get()]);}
 public function account(Request $r){return view('account',['user'=>$r->user(),'balance'=>Balance::firstOrCreate(['user_id'=>$r->user()->id]),'banks'=>DB::table('supported_banks')->where('active',true)->orderBy('name')->get()]);}
 public function transfer(Request $r){return view('transfer',['user'=>$r->user(),'balance'=>Balance::firstOrCreate(['user_id'=>$r->user()->id]),'transferFee'=>(int)(DB::table('system_settings')->where('key','bank_transfer_fee')->value('value')??0),'withdrawals'=>Withdrawal::where('user_id',$r->user()->id)->latest()->limit(30)->get()]);}
 public function history(Request $r){$id=$r->user()->id;return view('history',['payments'=>Payment::where('user_id',$id)->latest()->limit(50)->get(),'withdrawals'=>Withdrawal::where('user_id',$id)->latest()->limit(50)->get(),'escrows'=>Escrow::where('seller_id',$id)->latest()->limit(50)->get()]);}
 public function rekber(Request $r){return redirect('/history');}
 public function qrisPage(Request $r){$pg=DB::table('payment_provider_settings')->where('provider','gopay_marc')->first();return view('qris',['enabled'=>\Illuminate\Support\Facades\Storage::disk('local')->exists('private/gopay-qris.enc'),'payments'=>Payment::where('user_id',$r->user()->id)->latest()->limit(15)->get()]);}
 public function settings(Request $r,Wallet $w){$d=$r->validate(['fee_payer'=>'required|in:buyer,seller']);$r->user()->update($d);$w->audit($r->user()->id,'fee.preference','user',$r->user()->id,$d);return back()->with('ok','Preferensi fee diperbarui.');}
 public function avatar(Request $r,Wallet $w){$r->validate(['avatar'=>'required|image|mimes:jpeg,png,webp|max:2048']);$u=$r->user();$path=$r->file('avatar')->store('avatars','public');$old=$u->avatar_path;$u->update(['avatar_path'=>$path]);if($old)Storage::disk('public')->delete($old);$w->audit($u->id,'avatar.updated','user',$u->id);return back()->with('ok','Foto profil disimpan.');}
 public function bank(Request $r,Wallet $w){$d=$r->validate(['bank_code'=>['required',\Illuminate\Validation\Rule::exists('supported_banks','code')->where('active',true)],'bank_account'=>'required|digits_between:8,20']);$r->user()->update($d+['bank_name'=>null,'bank_verified_at'=>null]);$w->audit($r->user()->id,'bank.pending_verification','user',$r->user()->id,['bank_code'=>$d['bank_code']]);return back()->with('ok','Rekening tersimpan, belum terverifikasi. WD tersedia setelah rekening disetujui Super Admin atau diverifikasi melalui API bank.');}
 public function withdraw(Request $r,Wallet $w){
  $d=$r->validate(['amount'=>'required|integer|min:10000|max:1000000000']);
  $u=$r->user();
  abort_unless($u->bank_verified_at&&$u->bank_name,422,'Rekening belum diverifikasi.');
  DB::transaction(function()use($w,$u,$d){
   $b=$w->balance($u->id);
   $fee=(int)(DB::table('system_settings')->where('key','bank_transfer_fee')->value('value')??0);
   $total=$d['amount']+$fee;
   abort_if($b->available<$total,422,'Saldo tidak cukup untuk WD dan biaya transfer.');
   $wd=Withdrawal::create(['user_id'=>$u->id,'amount'=>$d['amount'],'transfer_fee'=>$fee,'bank_code'=>$u->bank_code,'bank_account'=>$u->bank_account,'bank_name'=>$u->bank_name,'verified_at'=>$u->bank_verified_at]);
   $w->move($b,'available',-$total,'withdrawal',$wd->id,'WD diajukan beserta biaya bank');
   $w->move($b,'locked',$total,'withdrawal',$wd->id,'WD dan biaya bank terkunci');
   $w->audit($u->id,'withdrawal.created','withdrawal',$wd->id,['transfer_fee'=>$fee]);
  });
  return back()->with('ok','Pengajuan WD dibuat.');
 }
 public function escrow(Request $r,Wallet $w){$d=$r->validate(['title'=>'required|string|max:150','amount'=>'required|integer|min:1000|max:1000000000']);$u=$r->user();$e=Escrow::create(['seller_id'=>$u->id,'title'=>$d['title'],'amount'=>$d['amount'],'fee'=>$w->fee('escrow',$d['amount']),'fee_payer'=>$u->fee_payer,'status'=>'pending','expires_at'=>now()->addDay()]);$w->audit($u->id,'escrow.created','escrow',$e->id);return back()->with('ok','TRX rekber dibuat; menunggu pembayaran terverifikasi.');}
 public function qris(Request $r,Wallet $w,\App\Services\StaticQris $qris,\App\Services\QrisAmountPayload $amountPayload){
  $d=$r->validate(['amount'=>'required|integer|min:1000|max:1000000000','fee_payer'=>'required|in:receiver']);
  $payload=$qris->payload();
  $fee=$w->fee('qris',(int)$d['amount']);
  $net=(int)$d['amount']-$fee;
  abort_if($net<=0,422,'Nominal tidak mencukupi.');
  // Nominal overlay is not guaranteed to be honored by every acquiring bank.
  $qr=$amountPayload->withAmount($payload,(int)$d['amount']);
  $payment=Payment::create(['user_id'=>$r->user()->id,'amount'=>(int)$d['amount'],'fee'=>$fee,'fee_payer'=>'receiver','gross'=>(int)$d['amount'],'net'=>$net,'status'=>'pending','provider'=>'qris_manual','qr_payload'=>$qr,'expires_at'=>now()->addDay()]);
  $w->audit($r->user()->id,'qris.manual_created','payment',$payment->id);
  return redirect()->route('qris.show',$payment);
 }

}
