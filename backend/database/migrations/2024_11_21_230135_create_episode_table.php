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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id('episode_id'); // Primary Key
            $table->unsignedBigInteger('season_id'); // Foreign key to seasons
            $table->foreign('season_id')->references('season_id')->on('seasons')->onDelete('cascade');
            $table->integer('episode_number');
            $table->string('title', 255);
            $table->time('duration');
            $table->date('release_date');
            $table->enum('quality', ['SD', 'HD', 'UHD']);
            $table->json('available_languages')->default(json_encode(['English']));
            $table->string('viewing_classification'); // For age restrictions (e.g., 12+)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
