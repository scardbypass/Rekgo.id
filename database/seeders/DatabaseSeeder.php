<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\FeeTier;
class DatabaseSeeder extends Seeder {public function run():void{foreach([['scope'=>'escrow','minimum'=>5000,'type'=>'flat','value'=>2000],['scope'=>'escrow','minimum'=>100000,'type'=>'flat','value'=>5000],['scope'=>'qris','minimum'=>0,'type'=>'flat','value'=>1000]] as $tier)FeeTier::updateOrCreate(['scope'=>$tier['scope'],'minimum'=>$tier['minimum']],$tier);}}
