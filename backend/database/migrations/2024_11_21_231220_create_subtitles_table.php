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
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id('subtitle_id');
            $table->unsignedBigInteger('episode_id')->nullable(); // Foreign key to episodes table
            $table->foreign('episode_id')->references('episode_id')->on('episodes')->onDelete('cascade'); // Ensure table name is 'episodes'

            $table->unsignedBigInteger('movie_id')->nullable(); // Foreign key to movies table
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('cascade'); // Ensure table name is 'movies'

            $table->string('subtitle_path', 255); // Path to subtitle file
            $table->string('language', 20); // Language of subtitle
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtitles'); // Correct table name here as 'subtitles'
    }
};
