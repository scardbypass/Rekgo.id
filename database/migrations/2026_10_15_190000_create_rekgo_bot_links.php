<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('rekgo_bot_requests',function(Blueprint $t){$t->id();$t->string('request_id',100)->unique();$t->string('group_jid',150);$t->string('seller_jid',150);$t->foreignId('seller_id')->constrained('users');$t->foreignId('escrow_id')->unique()->constrained('escrows');$t->timestamps();});
 }
 public function down():void {Schema::dropIfExists('rekgo_bot_requests');}
};