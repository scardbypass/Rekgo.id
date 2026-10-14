<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use Notifiable;
 protected $fillable=['name','username','email','phone','password','role','fee_payer','avatar_path','bank_code','bank_account','bank_name','bank_verified_at','suspended_at'];
 protected $hidden=['password','remember_token','bank_account'];
 protected $casts=['password'=>'hashed','bank_verified_at'=>'datetime','suspended_at'=>'datetime'];
 public function isAdmin(){return in_array($this->role,['super_admin','finance','support']);}
 public function balance(){return $this->hasOne(Balance::class);}
}
