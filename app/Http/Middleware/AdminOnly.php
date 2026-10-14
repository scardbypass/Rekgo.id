<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class AdminOnly {public function handle(Request $request,Closure $next){abort_unless(in_array($request->user()?->role,['super_admin','finance','support']),403);return $next($request);}}
