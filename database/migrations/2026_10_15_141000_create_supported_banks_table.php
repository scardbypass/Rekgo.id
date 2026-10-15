<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void {
  Schema::create('supported_banks',function(Blueprint $table){$table->id();$table->string('code',30)->unique();$table->string('name',100);$table->boolean('active')->default(true);$table->timestamps();});
  foreach(['BCA'=>'Bank Central Asia','BRI'=>'Bank Rakyat Indonesia','BNI'=>'Bank Negara Indonesia','MANDIRI'=>'Bank Mandiri'] as $code=>$name){DB::table('supported_banks')->insert(['code'=>$code,'name'=>$name,'active'=>true,'created_at'=>now(),'updated_at'=>now()]);}
 }
 public function down():void {Schema::dropIfExists('supported_banks');}
};
