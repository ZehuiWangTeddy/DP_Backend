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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id('profile_id');
            $table->unsignedBigInteger('user_id')->nullable(); // if delete user still keep profile data
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->string('name', length: 100);
            $table->string('photo_path', length: 255)->nullable();
            $table->boolean('child_profile')->default(false);
            $table->date('date_of_birth');
            $table->string('language', length: 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile');
    }
};
