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
        Schema::create('movie', function (Blueprint $table) {
            $table->id('movie_id');
            $table->string('title', length: 100);
            $table->time('duration'); //time or int, which better?
            $table->date('release_date');
            $table->string('quality');
            $table->integer('age_restriction');
            $table->string('genre', length: 100);
            $table->json('viewing_classification'); //array list
            $table->json('available_languages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie');
    }
};
