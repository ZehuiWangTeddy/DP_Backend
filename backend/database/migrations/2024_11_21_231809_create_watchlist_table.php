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
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id('watchlist_id'); // Primary Key
            
            // Foreign key to profiles
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->foreign('profile_id')->references('profile_id')->on('profiles')->onDelete('set null');

            // Foreign key to episodes
            $table->unsignedBigInteger('episode_id')->nullable();
            $table->foreign('episode_id')->references('episode_id')->on('episodes')->onDelete('cascade');

            // Foreign key to movies
            $table->unsignedBigInteger('movie_id')->nullable();
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('cascade');

            // Enum for viewing status
            $table->enum('viewing_status', ['to_watch', 'paused', 'finished'])->default('to_watch');

            // Add timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
