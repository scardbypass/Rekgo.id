<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ledger extends Model {public $timestamps=false;protected $fillable=['user_id','bucket','delta','reference_type','reference_id','memo','created_at'];}
