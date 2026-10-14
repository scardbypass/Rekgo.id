<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('wa_bot_instances',function(Blueprint $t){
   $t->id();$t->string('label',100);$t->string('phone',20)->unique();$t->string('connection_method',10)->default('qr');$t->string('status',20)->default('offline');$t->boolean('enabled')->default(true);$t->timestamp('last_seen_at')->nullable();$t->timestamps();
  });
  Schema::create('payment_provider_settings',function(Blueprint $t){
   $t->id();$t->string('provider')->unique();$t->boolean('enabled')->default(false);$t->boolean('on_demand')->default(true);$t->unsignedSmallInteger('poll_seconds')->default(60);$t->unsignedSmallInteger('expiry_minutes')->default(10);$t->string('merchant_id')->nullable();$t->timestamps();
  });
 }
 public function down():void {Schema::dropIfExists('payment_provider_settings');Schema::dropIfExists('wa_bot_instances');}
};
