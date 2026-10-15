<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::table('withdrawals',function(Blueprint $table){$table->unsignedBigInteger('transfer_fee')->default(0);});}
 public function down():void {Schema::table('withdrawals',function(Blueprint $table){$table->dropColumn('transfer_fee');});}
};
