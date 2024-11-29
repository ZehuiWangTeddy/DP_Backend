<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchhistories', function (Blueprint $table) {
            $table->id('history_id'); // Primary key
            
            // Foreign key to profiles
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->foreign('profile_id')->references('profile_id')->on('profiles')->onDelete('set null');
            
            // Foreign key to episodes
            $table->unsignedBigInteger('episode_id')->nullable();
            $table->foreign('episode_id')->references('episode_id')->on('episodes')->onDelete('set null');
            
            // Foreign key to movies
            $table->unsignedBigInteger('movie_id')->nullable();
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('set null');
            
            $table->time('resume_to'); // Save quit time
            $table->integer('times_watched'); // Times watched
            $table->dateTime('watched_time_stamp'); // When watched
            $table->enum('viewing_status', ['paused', 'finished']); // Status
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchhistory');
    }
};
