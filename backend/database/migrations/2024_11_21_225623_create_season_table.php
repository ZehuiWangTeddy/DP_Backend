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
        Schema::create('season', function (Blueprint $table) {
            $table->id('season_id');
            $table->unsignedBigInteger('series_id');
            $table->foreign('series_id')->references('series_id')->on('series')->onDelete('cascade');
            $table->integer('season_number');
            $table->date('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season');
    }
};
