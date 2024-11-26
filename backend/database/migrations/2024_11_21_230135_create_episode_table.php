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
        Schema::create('episode', function (Blueprint $table) {
            $table->id('episode_id');
            $table->unsignedBigInteger('season_id');
            $table->foreign('season_id')->references('season_id')->on('seasons')->onDelete('cascade');
            $table->integer('episode_number');
            $table->string('title');
            $table->json('quality'); // because have multiple quality
            $table->timestamp('duration');
            $table->json('available_languages');
            $table->date('release_date');
            $table->json('viewing_classification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode');
    }
};