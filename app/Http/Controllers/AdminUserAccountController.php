<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class AdminUserAccountController {
 private function authorizeAction(Request $r,User $user):void {
  abort_unless($r->user()?->role==='super_admin',403);
  abort_unless($user->role==='user',403,'Akun admin tidak dapat diubah lewat bantuan pengguna.');
  abort_if($r->user()->id===$user->id,403);
 }
 private function confirm(Request $r):void {
  abort_unless(Hash::check((string)$r->input('admin_password'),$r->user()->password),422,'Password Super Admin tidak sesuai.');
 }
 private function audit(Request $r,User $user,string $action,array $metadata=[]):void {
  Audit::create(['actor_id'=>$r->user()->id,'action'=>$action,'subject_type'=>'user','subject_id'=>$user->id,'metadata'=>$metadata,'ip'=>$r->ip(),'created_at'=>now()]);
 }
 public function contact(Request $r,User $user){
  $this->authorizeAction($r,$user);
  $d=$r->validate(['email'=>['required','email','max:255',Rule::unique('users','email')->ignore($user->id)],'phone'=>['required','regex:/^\\+?[0-9]{9,16}$/',Rule::unique('users','phone')->ignore($user->id)],'admin_password'=>'required|string']);
  $this->confirm($r);
  DB::transaction(function()use($r,$user,$d){
   $changes=[];
   if($user->email!==$d['email'])$changes[]='email';
   if($user->phone!==$d['phone'])$changes[]='phone';
   $user->update(['email'=>$d['email'],'phone'=>$d['phone']]);
   $this->audit($r,$user,'user.contact_updated',['fields'=>$changes]);
  });
  return back()->with('ok','Email dan nomor WhatsApp pengguna berhasil diperbarui.');
 }
 public function password(Request $r,User $user){
  $this->authorizeAction($r,$user);
  $d=$r->validate(['new_password'=>'required|string|min:12|max:128|confirmed','admin_password'=>'required|string']);
  $this->confirm($r);
  DB::transaction(function()use($r,$user,$d){
   $user->forceFill(['password'=>Hash::make($d['new_password']),'remember_token'=>Str::random(60)])->save();
   $this->audit($r,$user,'user.password_reset_by_admin');
  });
  return back()->with('ok','Password baru tersimpan. Sampaikan password melalui jalur aman dan minta pengguna menggantinya.');
 }
}
