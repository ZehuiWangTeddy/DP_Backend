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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id('preference_id'); // Primary key
            $table->unsignedBigInteger('profile_id')->nullable(); // if delete profile still keep preference data
            $table->foreign('profile_id')->references('profile_id')->on('profiles')->onDelete('set null');
            $table->string('content_type', 255); // Content type
            $table->json('genre')->default(json_encode([])); // Genre as JSON
            $table->integer('minimum_age')->default(0); // Minimum age restriction
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
