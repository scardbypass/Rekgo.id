<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::table('payments',function(Blueprint $t){$t->longText('qr_payload')->nullable();$t->string('provider_status',60)->nullable();$t->timestamp('last_checked_at')->nullable();$t->timestamp('next_check_at')->nullable()->index();$t->unsignedInteger('check_attempts')->default(0);});}
 public function down():void {Schema::table('payments',fn(Blueprint $t)=>$t->dropColumn(['qr_payload','provider_status','last_checked_at','next_check_at','check_attempts']));}
};
