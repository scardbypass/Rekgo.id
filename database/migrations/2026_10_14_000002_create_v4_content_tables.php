<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('system_settings',function(Blueprint $t){$t->id();$t->string('key')->unique();$t->text('value')->nullable();$t->timestamps();});
  Schema::create('announcements',function(Blueprint $t){$t->id();$t->string('title',180);$t->text('body')->nullable();$t->string('poster_path')->nullable();$t->boolean('active')->default(false);$t->timestamp('starts_at')->nullable();$t->timestamp('ends_at')->nullable();$t->timestamps();});
  Schema::create('announcement_reads',function(Blueprint $t){$t->id();$t->foreignId('announcement_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->timestamp('read_at');$t->unique(['announcement_id','user_id']);});
 }
 public function down():void {Schema::dropIfExists('announcement_reads');Schema::dropIfExists('announcements');Schema::dropIfExists('system_settings');}
};
