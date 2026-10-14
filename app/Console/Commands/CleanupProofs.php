<?php
namespace App\Console\Commands;
use App\Models\{Withdrawal,Audit};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class CleanupProofs extends Command {
 protected $signature='rekgo:cleanup-proofs {--dry-run}';protected $description='Remove eligible aged transfer proof files without deleting financial records';
 public function handle():int{if(!config('rekgo.proof_cleanup_enabled',false)){$this->warn('Proof cleanup disabled until dispute/legal-hold controls are verified.');return self::SUCCESS;} $days=max(45,(int)env('REKGO_WITHDRAW_PROOF_RETENTION_DAYS',45));$n=0;
 Withdrawal::where('status','success')->where('completed_at','<',now()->subDays($days))->whereNotNull('proof_path')->orderBy('id')->chunkById(100,function($items)use(&$n){foreach($items as $wd){if($this->option('dry-run')){$n++;continue;}if(Storage::disk('local')->delete($wd->proof_path)){$wd->update(['proof_path'=>null]);Audit::create(['actor_id'=>null,'action'=>'proof.retention_deleted','subject_type'=>'withdrawal','subject_id'=>$wd->id,'metadata'=>['retention'=>'completed withdrawal'],'ip'=>null]);$n++;}}});$this->info("Eligible proof files: $n");return self::SUCCESS;}
}
