<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {protected $fillable=['user_id','amount','fee','fee_payer','gross','net','status','provider','provider_ref','expires_at','paid_at','qr_payload','provider_status','last_checked_at','next_check_at','check_attempts'];protected $casts=['expires_at'=>'datetime','paid_at'=>'datetime','last_checked_at'=>'datetime','next_check_at'=>'datetime'];}
