<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Audit;
class V4AdminController {
 private function authorizeSuper(Request $r):void {abort_unless($r->user()?->role==='super_admin',403);}
 private function audit(Request $r,string $action,int $id=0):void {Audit::create(['actor_id'=>$r->user()->id,'action'=>$action,'subject_type'=>'v4_settings','subject_id'=>$id,'metadata'=>[],'ip'=>$r->ip(),'created_at'=>now()]);}
 public function settings(Request $r){$this->authorizeSuper($r);return view('admin-v4',['settings'=>DB::table('system_settings')->pluck('value','key'),'announcements'=>DB::table('announcements')->latest()->get()]);}
 public function maintenance(Request $r){$this->authorizeSuper($r);$d=$r->validate(['enabled'=>'nullable|boolean','message'=>'required|string|max:1000']);foreach(['maintenance_enabled'=>($r->boolean('enabled')?'1':'0'),'maintenance_message'=>$d['message']] as $key=>$value){DB::table('system_settings')->updateOrInsert(['key'=>$key],['value'=>$value,'updated_at'=>now(),'created_at'=>now()]);}$this->audit($r,'maintenance.updated');return back()->with('ok','Maintenance diperbarui.');}
 public function createAnnouncement(Request $r){$this->authorizeSuper($r);$d=$r->validate(['title'=>'required|string|max:180','body'=>'nullable|string|max:5000','poster'=>'nullable|file|mimes:jpg,jpeg,png,webp|max:5120','active'=>'nullable|boolean','starts_at'=>'nullable|date','ends_at'=>'nullable|date|after:starts_at']);$path=$r->hasFile('poster')?$r->file('poster')->store('announcements','public'):null;$id=DB::table('announcements')->insertGetId(['title'=>$d['title'],'body'=>$d['body']??null,'poster_path'=>$path,'active'=>$r->boolean('active'),'starts_at'=>$d['starts_at']??null,'ends_at'=>$d['ends_at']??null,'created_at'=>now(),'updated_at'=>now()]);$this->audit($r,'announcement.created',$id);return back()->with('ok','Pengumuman dibuat.');}
 public function toggleAnnouncement(Request $r,int $id){$this->authorizeSuper($r);$d=$r->validate(['active'=>'required|boolean']);abort_unless(DB::table('announcements')->where('id',$id)->update(['active'=>$d['active'],'updated_at'=>now()]),404);$this->audit($r,'announcement.toggled',$id);return back()->with('ok','Status pengumuman diperbarui.');}
 public function deleteAnnouncement(Request $r,int $id){$this->authorizeSuper($r);$a=DB::table('announcements')->where('id',$id)->first();abort_unless($a,404);DB::table('announcements')->where('id',$id)->delete();if($a->poster_path)Storage::disk('public')->delete($a->poster_path);$this->audit($r,'announcement.deleted',$id);return back()->with('ok','Pengumuman dihapus.');}
 public function dismiss(Request $r,int $id){abort_unless(DB::table('announcements')->where('id',$id)->where('active',true)->exists(),404);DB::table('announcement_reads')->updateOrInsert(['announcement_id'=>$id,'user_id'=>$r->user()->id],['read_at'=>now()]);return response()->noContent();}
}
