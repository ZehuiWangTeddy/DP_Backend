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
        Schema::create('preference', function (Blueprint $table) {
            $table->id('preference_id');
            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')->references('profile_id')->on('profiles')->onDelete('cascade');
            $table->json('content_type'); // save available viewing classification
            $table->json('genre'); // several genres, json is good? if json good, need default value like genre:[]?
            $table->integer('minimum_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preference');
    }
};
