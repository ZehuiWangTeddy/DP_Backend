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
        Schema::create('movies', function (Blueprint $table) {
            $table->id('movie_id');
            $table->string('title', length: 100);
            $table->timestamp('duration');
            $table->date('release_date');
            $table->json('quality')->default(json_encode([])); // because have multiple quality
            $table->integer('age_restriction');
            $table->json('genre');
            $table->json('viewing_classification'); //array list
            $table->json('available_languages')->default(json_encode([]));
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
