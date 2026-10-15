<?php
namespace App\Services;
use App\Models\{Balance,Ledger,Audit,FeeTier};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class Wallet {
 public function balance(int $id):Balance {Balance::firstOrCreate(['user_id'=>$id]);return Balance::where('user_id',$id)->lockForUpdate()->firstOrFail();}
 public function move(Balance $balance,string $bucket,int $delta,string $type,int $id,string $memo):void{
  $current=(int)$balance->$bucket;
  if($current+$delta<0)throw ValidationException::withMessages(['amount'=>'Saldo tidak mencukupi.']);
  $balance->$bucket=$current+$delta;$balance->save();
  Ledger::create(['user_id'=>$balance->user_id,'bucket'=>$bucket,'delta'=>$delta,'reference_type'=>$type,'reference_id'=>$id,'memo'=>$memo]);
 }
 public function audit(?int $actor,string $action,string $type,int $id,array $metadata=[]):void{Audit::create(['actor_id'=>$actor,'action'=>$action,'subject_type'=>$type,'subject_id'=>$id,'metadata'=>$metadata,'ip'=>request()->ip()]);}
 public function fee(string $scope,int $amount):int{$tier=FeeTier::where('scope',$scope)->where('active',true)->where('minimum','<=',$amount)->where(function($q)use($amount){$q->whereNull('maximum')->orWhere('maximum','>=',$amount);})->orderByDesc('minimum')->first();return !$tier?0:($tier->type==='flat'?(int)$tier->value:(int)ceil($amount*$tier->value/10000));}
}
