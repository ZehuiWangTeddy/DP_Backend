<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id('subscription_id');
            $table->unsignedBigInteger('user_id')->nullable(); //// if delete user still keep subscription data
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->double('price')->comment("price IN ('7.99', '10.99', '13.99')");
            $table->string('name', length: 5)->comment("name IN ('SD', 'HD', 'UHD')");
            $table->enum('status', ['paid', 'expired']); //paid and expired?
            $table->date('start_date');
            $table->date('end_date');
            $table->string('payment_method')->comment("payment_method IN ('PayPal', 'Visa', 'MasterCard', 'Apple Pay', 'Google Pay', 'iDEAL')");//use enum amd add validation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription');
    }
};
