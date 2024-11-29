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
        Schema::create('watchhistory', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('profile_id')->nullable(); // if delete profile still keep watch history data
            $table->foreign('profile_id')->references('profile_id')->on('profiles')->onDelete('set null');
            $table->unsignedBigInteger('episode_id')->nullable();
            $table->foreign('episode_id')->references('episode_id')->on('episode')->onDelete('set null');
            $table->unsignedBigInteger('movie_id')->nullable();
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('set null');
            $table->timestamp('resume_to'); // save quit time
            $table->integer('times_watched');
            $table->dateTime('watched_time_stamp'); // save when you watched
            $table->enum('viewing_status', ['paused', 'finished']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchhistory');
    }
};
