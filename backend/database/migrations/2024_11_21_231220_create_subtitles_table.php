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
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id('subtitle_id');
            $table->unsignedBigInteger('episode_id')->nullable(); // if delete episode then delete subtitle
            $table->foreign('episode_id')->references('episode_id')->on('episode')->onDelete('cascade');
            $table->unsignedBigInteger('movie_id')->nullable(); // if delete movie then delete subtitle
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('cascade');
            $table->string('subtitle_path', length: 255);
            $table->string('language', length: 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtitle');
    }
};
