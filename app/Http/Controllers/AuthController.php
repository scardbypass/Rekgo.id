<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController {
 public function loginForm(){return view('auth');}
 public function register(Request $r){$d=$r->validate(['name'=>'required|string|max:100','username'=>'required|alpha_dash|max:50|unique:users','email'=>'required|email|unique:users','phone'=>'required|string|regex:/^\\+?[0-9]{9,16}$/|max:20|unique:users','password'=>'required|string|min:12|confirmed']);$u=User::create($d+['role'=>'user']);Auth::login($u);$r->session()->regenerate();return redirect('/dashboard');}
 public function login(Request $r){$d=$r->validate(['identity'=>'required|string','password'=>'required|string']);$field=filter_var($d['identity'],FILTER_VALIDATE_EMAIL)?'email':(preg_match('/^[0-9+]{9,16}$/',$d['identity'])?'phone':'username');if(!Auth::attempt([$field=>$d['identity'],'password'=>$d['password'],'suspended_at'=>null],$r->boolean('remember')))return back()->withErrors(['identity'=>'Kredensial tidak valid atau akun nonaktif.']);$r->session()->regenerate();return redirect('/dashboard');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/login');}
}
