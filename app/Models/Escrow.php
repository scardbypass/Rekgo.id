<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Escrow extends Model {protected $fillable=['seller_id','buyer_id','title','amount','fee','fee_payer','status','expires_at','completed_at'];protected $casts=['expires_at'=>'datetime','completed_at'=>'datetime'];public function seller(){return $this->belongsTo(User::class,'seller_id');}}
