<?php
use Illuminate\Support\Facades\Schedule;
Schedule::command('rekgo:cleanup-proofs')->dailyAt('03:00')->timezone('Asia/Jakarta');
\Illuminate\Support\Facades\Artisan::command('rekgo:poll-qris',function(){
 $pg=\Illuminate\Support\Facades\DB::table('payment_provider_settings')->where('provider','gopay_marc')->first();if(!$pg||!$pg->enabled)return;
 $service=app(\App\Services\GopayPayments::class);
 \App\Models\Payment::where('status','pending')->where('provider','gopay_marc')->whereNotNull('provider_ref')->where(function($q){$q->whereNull('next_check_at')->orWhere('next_check_at','<=',now());})->orderBy('id')->limit(50)->get()->each(function($p)use($service){try{$p->expires_at && $p->expires_at->isPast() ? $service->reconcileExpiry($p) : $service->check($p);}catch(\Throwable $e){\Illuminate\Support\Facades\Log::warning('QRIS check failed',['id'=>$p->id,'error'=>$e->getMessage()]);\Illuminate\Support\Facades\DB::table('payments')->where('id',$p->id)->update(['next_check_at'=>now()->addMinute()]);}});
})->purpose('Check only active GoPay QRIS payments');
Schedule::command('rekgo:poll-qris')->everyMinute()->withoutOverlapping();
