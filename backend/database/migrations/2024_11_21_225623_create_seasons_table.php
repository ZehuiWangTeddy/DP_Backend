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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id('season_id');
            $table->unsignedBigInteger('series_id')->nullable(); // if delete series still keep season data
            $table->foreign('series_id')->references('series_id')->on('series')->onDelete('set null');
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
