<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Audit extends Model {public $timestamps=false;protected $fillable=['actor_id','action','subject_type','subject_id','metadata','ip','created_at'];protected $casts=['metadata'=>'array'];}
