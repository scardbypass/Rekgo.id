<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class BankDirectoryController {
 private function guard(Request $r):void {abort_unless($r->user()?->role==='super_admin',403);}
 public function index(Request $r){$this->guard($r);return view('admin-banks',['banks'=>DB::table('supported_banks')->orderBy('name')->get()]);}
 public function store(Request $r){$this->guard($r);$d=$r->validate(['code'=>'required|alpha_dash|max:30|unique:supported_banks,code','name'=>'required|string|max:100']);DB::table('supported_banks')->insert(['code'=>strtoupper($d['code']),'name'=>trim($d['name']),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);return back()->with('ok','Bank ditambahkan.');}
 public function remove(Request $r,int $id){$this->guard($r);abort_unless(DB::table('supported_banks')->where('id',$id)->update(['active'=>false,'updated_at'=>now()]),404);return back()->with('ok','Bank disembunyikan dari pilihan baru. Riwayat lama tetap tersimpan.');}
 public function restore(Request $r,int $id){$this->guard($r);abort_unless(DB::table('supported_banks')->where('id',$id)->update(['active'=>true,'updated_at'=>now()]),404);return back()->with('ok','Bank diaktifkan kembali.');}
}
