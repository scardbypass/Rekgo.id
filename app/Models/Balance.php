<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Balance extends Model {protected $fillable=['user_id','available','pending','locked'];protected $casts=['available'=>'integer','pending'=>'integer','locked'=>'integer'];}
