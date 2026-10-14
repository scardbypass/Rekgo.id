<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class MaintenanceMode {
 public function handle(Request $request,Closure $next){
  if(!Schema::hasTable('system_settings')) return $next($request);
  $active=DB::table('system_settings')->where('key','maintenance_enabled')->value('value')==='1';
  if(!$active || $request->is('admin*') || $request->is('login') || $request->is('logout') || $request->is('up') || ($request->user() && $request->user()->role==='super_admin')) return $next($request);
  $msg=DB::table('system_settings')->where('key','maintenance_message')->value('value') ?: 'RekGo sedang dalam perbaikan.';
  if($request->expectsJson()) return response()->json(['message'=>$msg],503,['Retry-After'=>'300']);
  return response()->view('maintenance',['message'=>$msg],503,['Retry-After'=>'300']);
 }
}
