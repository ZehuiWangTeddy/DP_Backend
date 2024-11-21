<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('email', length: 100)->unique();
            $table->string('password', length: 100);
            $table->string('name', length: 100);
            $table->string('address', length: 100);
            $table->integer('failed_login_attempts');
            $table->boolean('active'); //need default?
            $table->string('sent_referral_code', length: 10); // generate when create account
            $table->string('received_referral_code', length: 10)->nullable();
            $table->boolean('has_discount');
            $table->dateTime('locked_until', precision: 0)->default(null);
            $table->boolean('trial_available'); //need default?
            $table->boolean('user_role')->default('1'); // 0 is admin, 1 is normal user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
