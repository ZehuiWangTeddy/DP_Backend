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
        Schema::create('series', function (Blueprint $table) {
            $table->id('series_id');
            $table->string('title', 255);
            $table->integer('age_restriction');
            $table->date('release_date');
            $table->string('genre', 255);
            $table->string('viewing_classification', 20); // Classification from ViewingClassificationHelper
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
