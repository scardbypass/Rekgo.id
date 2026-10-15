<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeeTier extends Model {protected $fillable=['scope','minimum','maximum','type','value','active'];}
