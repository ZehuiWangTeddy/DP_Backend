<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'series_id' => \App\Models\Series::factory(), // Relates to Series
            'season_number' => $this->faker->numberBetween(1, 10),
            'release_date' => $this->faker->date(),
        ];
    }
}
