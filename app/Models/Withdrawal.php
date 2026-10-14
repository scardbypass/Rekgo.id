<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Withdrawal extends Model {protected $fillable=['user_id','amount','status','bank_code','bank_account','bank_name','verified_at','processed_by','completed_by','rejected_reason','proof_path','bank_reference','notes','completed_at'];protected $casts=['verified_at'=>'datetime','completed_at'=>'datetime'];public function user(){return $this->belongsTo(User::class);}}
