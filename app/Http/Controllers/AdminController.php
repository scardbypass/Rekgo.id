<?php
namespace App\Http\Controllers;
use App\Models\{User,Balance,Withdrawal,Escrow,Payment,Audit,FeeTier};
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class AdminController {
 private function finance(Request $r){abort_unless(in_array($r->user()->role,['super_admin','finance']),403);}
 private function super(Request $r){abort_unless($r->user()->role==='super_admin',403);}
 public function dashboard(Request $r){return view('admin',['users'=>User::latest()->limit(25)->get(),'withdrawals'=>Withdrawal::with('user')->latest()->limit(40)->get(),'escrows'=>Escrow::latest()->limit(25)->get(),'payments'=>Payment::latest()->limit(25)->get(),'audits'=>Audit::latest('id')->limit(30)->get(),'tiers'=>FeeTier::orderBy('scope')->orderBy('minimum')->get(),'balances'=>Balance::selectRaw('SUM(available) available,SUM(pending) pending,SUM(locked) locked')->first()]);}
 public function page(Request $r,string $page){abort_unless(in_array($page,['acc','users','fee','admins','audits']),404);return view('admin-page',['page'=>$page,'users'=>User::latest()->limit(25)->get(),'withdrawals'=>Withdrawal::with('user')->latest()->limit(40)->get(),'tiers'=>FeeTier::orderBy('scope')->orderBy('minimum')->get(),'audits'=>Audit::latest('id')->limit(30)->get()]);}
 public function userDetail(Request $r,User $user){$this->finance($r);return view('admin-user-detail',['member'=>$user,'balance'=>Balance::where('user_id',$user->id)->first(),'payments'=>Payment::where('user_id',$user->id)->latest()->limit(20)->get(),'withdrawals'=>Withdrawal::where('user_id',$user->id)->latest()->limit(20)->get()]);}
 public function adjustBalance(Request $r,User $user,Wallet $w){
  $this->super($r);
  $d=$r->validate(['operation'=>'required|in:add,subtract','amount'=>'required|integer|min:1|max:100000000','reason'=>'required|string|min:10|max:500']);
  $delta=$d['operation']==='add'?(int)$d['amount']:-(int)$d['amount'];
  DB::transaction(function()use($r,$user,$w,$d,$delta){
   $balance=$w->balance($user->id);
   $w->move($balance,'available',$delta,'admin_adjustment',$user->id,'Manual adjustment: '.$d['reason']);
   $w->audit($r->user()->id,'balance.manual_adjustment','user',$user->id,['operation'=>$d['operation'],'amount'=>$d['amount'],'reason'=>$d['reason'],'balance_after'=>$balance->available]);
  },3);
  return redirect('/admin/users/'.$user->id)->with('ok','Penyesuaian saldo berhasil dan tercatat di ledger.');
 }
 public function verifyUserBank(Request $r,User $user,Wallet $w){
  $this->super($r);
  $d=$r->validate(['decision'=>'required|in:approve,reject','bank_name'=>'required_if:decision,approve|nullable|string|min:3|max:120','reason'=>'required_if:decision,reject|nullable|string|max:500']);
  DB::transaction(function()use($r,$user,$w,$d){
   $locked=User::whereKey($user->id)->lockForUpdate()->firstOrFail();
   abort_unless($locked->bank_code && $locked->bank_account,422,'Pengguna belum menyimpan rekening.');
   if($d['decision']==='approve'){
    $locked->bank_name=trim($d['bank_name']);
    $locked->bank_verified_at=now();
   }else{
    $locked->bank_name=null;
    $locked->bank_verified_at=null;
   }
   $locked->save();
   $w->audit($r->user()->id,'bank.manual_'.$d['decision'],'user',$locked->id,[
    'bank_code'=>$locked->bank_code,'account_last4'=>substr($locked->bank_account,-4),
    'account_name'=>$locked->bank_name,'reason'=>$d['reason']??null,'method'=>'manual'
   ]);
  },3);
  return back()->with('ok',$d['decision']==='approve'?'Rekening disetujui manual. Nama penerima kini tampil dan WD dapat diajukan.':'Verifikasi rekening ditolak/dicabut.');
 }
 public function verify(Request $r,Withdrawal $wd,Wallet $w){$this->finance($r);abort_unless($wd->status==='pending',409);abort(501,'Verifikasi nama rekening membutuhkan integrasi API bank resmi; tidak bisa disetujui secara manual sebagai hasil inquiry.');}
 public function process(Request $r,Withdrawal $wd,Wallet $w){$this->finance($r);DB::transaction(function()use($r,$wd,$w){$wd=Withdrawal::whereKey($wd->id)->lockForUpdate()->firstOrFail();abort_unless($wd->status==='pending'&&$wd->verified_at&&$wd->bank_name,409,'Rekening belum terverifikasi.');$wd->update(['status'=>'processing','processed_by'=>$r->user()->id]);$w->audit($r->user()->id,'withdrawal.processing','withdrawal',$wd->id);});return back()->with('ok','WD masuk Proses.');}
 public function success(Request $r,Withdrawal $wd,Wallet $w){$this->finance($r);$d=$r->validate(['bank_reference'=>'required|string|max:150','proof'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120','notes'=>'nullable|string|max:1000']);DB::transaction(function()use($r,$wd,$w,$d){$wd=Withdrawal::whereKey($wd->id)->lockForUpdate()->firstOrFail();abort_unless($wd->status==='processing',409);$b=$w->balance($wd->user_id);$proof=$r->hasFile('proof')?$r->file('proof')->store('transfer-proofs','local'):null;$wd->update(['status'=>'success','bank_reference'=>$d['bank_reference'],'proof_path'=>$proof,'notes'=>$d['notes']??null,'completed_by'=>$r->user()->id,'completed_at'=>now()]);$w->move($b,'locked',-($wd->amount+$wd->transfer_fee),'withdrawal',$wd->id,'WD berhasil dibayar termasuk biaya transfer');$w->audit($r->user()->id,'withdrawal.success','withdrawal',$wd->id,['bank_reference'=>$d['bank_reference']]);});return back()->with('ok','Transfer selesai dan ledger diperbarui. Pastikan transfer bank sungguhan telah diverifikasi sebelum menekan Success.');}
 public function reject(Request $r,Withdrawal $wd,Wallet $w){$this->finance($r);$d=$r->validate(['reason'=>'required|string|min:5|max:1000']);DB::transaction(function()use($r,$wd,$w,$d){$wd=Withdrawal::whereKey($wd->id)->lockForUpdate()->firstOrFail();abort_unless($wd->status==='pending',409,'WD yang sedang diproses harus direkonsiliasi sebelum dibatalkan.');$b=$w->balance($wd->user_id);$refund=$wd->amount+$wd->transfer_fee;$w->move($b,'locked',-$refund,'withdrawal',$wd->id,'WD ditolak');$w->move($b,'available',$refund,'withdrawal',$wd->id,'Pengembalian WD termasuk biaya transfer');$wd->update(['status'=>'rejected','rejected_reason'=>$d['reason'],'processed_by'=>$r->user()->id]);$w->audit($r->user()->id,'withdrawal.rejected','withdrawal',$wd->id,['reason'=>$d['reason']]);});return back()->with('ok','WD ditolak; saldo dikembalikan.');}
 public function proof(Request $r,Withdrawal $wd,Wallet $w){$this->finance($r);abort_unless($wd->proof_path&&Storage::disk('local')->exists($wd->proof_path),404);$w->audit($r->user()->id,'withdrawal.proof_view','withdrawal',$wd->id);return Storage::disk('local')->download($wd->proof_path);}
 public function addAdmin(Request $r,Wallet $w){$this->super($r);$d=$r->validate(['name'=>'required|string|max:100','username'=>'required|alpha_dash|unique:users','email'=>'required|email|unique:users','role'=>'required|in:super_admin,finance,support']);$temp=Str::random(40);$u=User::create($d+['password'=>$temp]);$w->audit($r->user()->id,'admin.created','user',$u->id,['role'=>$u->role]);return back()->with('ok','Akun admin dibuat dalam keadaan belum dapat diakses. Integrasikan email undangan + reset password aman sebelum aktivasi.');}
 public function bankTransferFee(Request $r,Wallet $w){$this->super($r);$d=$r->validate(['fee'=>'required|integer|min:0|max:1000000']);DB::table('system_settings')->updateOrInsert(['key'=>'bank_transfer_fee'],['value'=>(string)$d['fee'],'created_at'=>now(),'updated_at'=>now()]);$w->audit($r->user()->id,'bank_transfer_fee.updated','system_settings',0,['fee'=>$d['fee']]);return back()->with('ok','Biaya transfer bank berhasil diperbarui.');}
 public function tier(Request $r,Wallet $w){
  $this->super($r);
  $d=$r->validate(['scope'=>'required|in:escrow,qris','minimum'=>'required|integer|min:0','maximum'=>'required|integer|gte:minimum|max:1000000000','type'=>'required|in:flat,percent','value'=>'required|integer|min:0|max:10000000']);
  abort_if($d['type']==='percent'&&$d['value']>10000,422,'Persentase maksimal 100%.');
  DB::transaction(function()use($d,$r,$w){
   $existing=FeeTier::where('scope',$d['scope'])->where('minimum',$d['minimum'])->lockForUpdate()->first();
   $overlap=FeeTier::where('scope',$d['scope'])->where('active',true)->where('minimum','<=',$d['maximum'])->where(function($q)use($d){$q->whereNull('maximum')->orWhere('maximum','>=',$d['minimum']);});
   if($existing)$overlap->where('id','!=',$existing->id);
   abort_if($overlap->exists(),422,'Rentang nominal bertabrakan dengan tier yang sudah ada.');
   $tier=FeeTier::updateOrCreate(['scope'=>$d['scope'],'minimum'=>$d['minimum']],$d+['active'=>true]);
   $w->audit($r->user()->id,'fee.updated','fee_tier',$tier->id,$d);
  });
  return back()->with('ok','Tarif per transaksi berhasil disimpan.');
 }
 public function updateTier(Request $r,FeeTier $tier,Wallet $w){
  $this->super($r);
  $d=$r->validate(['scope'=>'required|in:escrow,qris','minimum'=>'required|integer|min:0','maximum'=>'required|integer|gte:minimum|max:1000000000','type'=>'required|in:flat,percent','value'=>'required|integer|min:0|max:10000000']);
  abort_if($d['type']==='percent' && $d['value']>10000,422,'Persentase maksimal 100%.');
  DB::transaction(function()use($r,$tier,$d,$w){
   $locked=FeeTier::whereKey($tier->id)->lockForUpdate()->firstOrFail();
   $overlap=FeeTier::where('scope',$d['scope'])->where('active',true)->where('id','!=',$locked->id)->where('minimum','<=',$d['maximum'])->where(function($q)use($d){$q->whereNull('maximum')->orWhere('maximum','>=',$d['minimum']);});
   abort_if($overlap->exists(),422,'Rentang nominal bertabrakan dengan tarif lain.');
   $locked->update($d);
   $w->audit($r->user()->id,'fee.updated','fee_tier',$locked->id,$d);
  },3);
  return back()->with('ok','Tarif berhasil diedit.');
 }
 public function deleteTier(Request $r,FeeTier $tier,Wallet $w){
  $this->super($r);$tier->update(['active'=>false]);$w->audit($r->user()->id,'fee.disabled','fee_tier',$tier->id);return back()->with('ok','Tarif dinonaktifkan.');
 }

}
