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
            $table->string('email', 100)->unique();
            $table->string('password', 100);
            $table->string('name', 100);
            $table->string('address', 255)->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->boolean('active')->default(true);
            $table->string('sent_referral_code', 10); // Nullable for flexibility
            $table->string('received_referral_code', 10)->nullable();
            $table->boolean('has_discount')->default(false);
            $table->dateTime('locked_until')->nullable(); // Default is unnecessary for nullable
            $table->boolean('trial_available')->default(true);
            $table->tinyInteger('user_role')->default(1); // 0 is admin, 1 is normal user
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
