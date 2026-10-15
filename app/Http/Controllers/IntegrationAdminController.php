<?php
namespace App\Http\Controllers;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
class IntegrationAdminController {
 private function authorizeSuper(Request $r):void {abort_unless($r->user()?->role==='super_admin',403);}
 private function audit(Request $r,string $action,string $subject,int $id,array $meta=[]):void {
  Audit::create(['actor_id'=>$r->user()->id,'action'=>$action,'subject_type'=>$subject,'subject_id'=>$id,'metadata'=>$meta,'ip'=>$r->ip(),'created_at'=>now()]);
 }
 public function index(Request $r){$this->authorizeSuper($r);return view('admin-integrations',['bots'=>DB::table('wa_bot_instances')->orderByDesc('id')->get(),'pg'=>DB::table('payment_provider_settings')->where('provider','gopay_marc')->first()]);}
 public function bots(Request $r){$this->authorizeSuper($r);return view('admin-integrations-bots',['bots'=>DB::table('wa_bot_instances')->orderByDesc('id')->get()]);}
 public function gopay(Request $r){$this->authorizeSuper($r);$disk=Storage::disk('local');$qr='';if($disk->exists('private/gopay-qris.enc')){try{$qr=Crypt::decryptString($disk->get('private/gopay-qris.enc'));}catch(\Throwable $e){report($e);}}return view('admin-integrations-gopay',['pg'=>DB::table('payment_provider_settings')->where('provider','gopay_marc')->first(),'credentials_saved'=>$disk->exists('private/gopay-merchant.enc'),'qris_text'=>$qr]);}
 public function deleteBot(Request $r,int $id){$this->authorizeSuper($r);$bot=DB::table('wa_bot_instances')->where('id',$id)->first();abort_unless($bot,404);abort_if($bot->status==='online',422,'Nonaktifkan dan putuskan koneksi bot sebelum menghapus.');DB::transaction(function()use($r,$id,$bot){DB::table('wa_bot_instances')->where('id',$id)->delete();$this->audit($r,'wa_bot.deleted','wa_bot',$id,['phone_suffix'=>substr($bot->phone,-4)]);});return redirect('/admin/integrations/bots')->with('ok','Bot berhasil dihapus dari daftar. Pastikan sesi pada bridge WhatsApp juga sudah dicabut.');}
 public function saveGopayCredentials(Request $r){
  $this->authorizeSuper($r);
  $d=$r->validate(['email'=>'required|email|max:254','password'=>'nullable|string|max:512']);
  $disk=Storage::disk('local');$path='private/gopay-merchant.enc';
  $old=[];if($disk->exists($path)){try{$old=json_decode(Crypt::decryptString($disk->get($path)),true)?:[];}catch(\Throwable $e){abort(500,'Konfigurasi terenkripsi tidak dapat dibaca.');}}
  if(empty($d['password']) && empty($old['password']))return back()->withErrors(['password'=>'Password wajib untuk koneksi pertama.']);
  $payload=['email'=>$d['email'],'password'=>$d['password']??$old['password'],'updated_at'=>now()->toIso8601String()];
  $disk->put($path,Crypt::encryptString(json_encode($payload,JSON_THROW_ON_ERROR)));
  $this->audit($r,'pg.credentials_saved','payment_provider',0);
  return redirect('/admin/integrations/gopay')->with('ok','Kredensial terenkripsi tersimpan di server. Belum terhubung sampai adapter GoBiz berhasil diuji.');
 }
 public function saveQrisText(Request $r){$this->authorizeSuper($r);$d=$r->validate(['qris_text'=>'required|string|min:20|max:4096']);$qr=trim($d['qris_text']);if(!str_starts_with($qr,'000201') || !preg_match('/6304([0-9A-Fa-f]{4})$/',$qr,$match))return back()->withErrors(['qris_text'=>'Format QRIS tidak valid: awalan EMV atau CRC tidak ditemukan.'])->withInput();
  $crc=0xFFFF;$body=substr($qr,0,-4);
  foreach(unpack('C*',$body) as $byte){$crc^=$byte<<8;for($i=0;$i<8;$i++){$crc=($crc&0x8000)?(($crc<<1)^0x1021)&0xFFFF:($crc<<1)&0xFFFF;}}
  if(strtoupper(sprintf('%04X',$crc))!==strtoupper($match[1]))return back()->withErrors(['qris_text'=>'Checksum CRC QRIS tidak cocok. Salin ulang payload lengkap dari QRIS merchant.'])->withInput();Storage::disk('local')->put('private/gopay-qris.enc',Crypt::encryptString($qr));$this->audit($r,'pg.static_qris_updated','payment_provider',0);return redirect('/admin/integrations/gopay')->with('ok','QRIS Text disimpan terenkripsi. Belum aktif untuk pembayaran otomatis.');}
 public function addBot(Request $r){$this->authorizeSuper($r);$d=$r->validate(['label'=>'required|string|max:100','phone'=>'required|regex:/^62[0-9]{8,13}$/|unique:wa_bot_instances,phone','connection_method'=>'required|in:qr,pair']);
  $id=DB::table('wa_bot_instances')->insertGetId($d+['status'=>'offline','enabled'=>true,'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'wa_bot.registered','wa_bot',$id,['phone_suffix'=>substr($d['phone'],-4)]);return back()->with('ok','Nomor bot terdaftar. QR/pairing memerlukan service Baileys yang belum terpasang.');}
 public function toggleBot(Request $r,int $id){$this->authorizeSuper($r);$d=$r->validate(['enabled'=>'required|boolean']);abort_unless(DB::table('wa_bot_instances')->where('id',$id)->update(['enabled'=>$d['enabled'],'updated_at'=>now()]),404);$this->audit($r,'wa_bot.enabled_changed','wa_bot',$id,['enabled'=>$d['enabled']]);return back()->with('ok','Pengaturan bot diperbarui.');}
 public function savePg(Request $r){$this->authorizeSuper($r);$d=$r->validate(['enabled'=>'nullable|boolean','on_demand'=>'nullable|boolean','poll_seconds'=>'required|integer|in:30,60,120','expiry_minutes'=>'required|integer|min:5|max:60','merchant_id'=>'nullable|string|max:150']);
  // Never enable live payment acceptance without an installed, verified gateway adapter.
  if($r->boolean('enabled')&&!config('rekgo.gopay_live_verified'))return back()->withErrors(['enabled'=>'Pembayaran live belum bisa diaktifkan. Adapter jurnal GoPay dan rekonsiliasi wajib diuji terlebih dahulu.'])->withInput();
  DB::table('payment_provider_settings')->updateOrInsert(['provider'=>'gopay_marc'],['enabled'=>$r->boolean('enabled'),'on_demand'=>$r->boolean('on_demand'),'poll_seconds'=>$d['poll_seconds'],'expiry_minutes'=>$d['expiry_minutes'],'merchant_id'=>$d['merchant_id']??null,'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'pg.configuration_updated','payment_provider',0);return back()->with('ok','Konfigurasi PG disimpan. Status ON hanya jika integrasi sudah diuji dan diizinkan.');}
 public function requestOtp(Request $r,\App\Services\GopayBridge $bridge){$this->authorizeSuper($r);$d=$r->validate(['phone'=>'required|regex:/^62[0-9]{8,13}$/']);$result=$bridge->call('login-request',['phone'=>$d['phone']]);$token=$result['otp_token']??null;abort_unless(is_string($token)&&$token!=='',502,'OTP token tidak ditemukan');$r->session()->put('gopay_otp_token',encrypt($token));$r->session()->put('gopay_otp_expires',now()->addMinutes(5)->timestamp);$this->audit($r,'pg.otp_requested','payment_provider',0);return back()->with('ok','OTP diminta. Periksa nomor merchant.');}
 public function verifyOtp(Request $r,\App\Services\GopayBridge $bridge){$this->authorizeSuper($r);$d=$r->validate(['otp'=>'required|digits_between:4,8']);abort_unless($r->session()->get('gopay_otp_expires',0)>time(),422,'OTP kedaluwarsa.');$token=decrypt($r->session()->get('gopay_otp_token'));$bridge->call('login-verify',['otp_token'=>$token,'otp'=>$d['otp']]);$r->session()->forget(['gopay_otp_token','gopay_otp_expires']);$this->audit($r,'pg.otp_verified','payment_provider',0);return back()->with('ok','OTP diverifikasi oleh bridge. Uji QRIS sebelum mengaktifkan live.');}
}
