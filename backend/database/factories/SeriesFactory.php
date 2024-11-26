<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Series>
 */
class SeriesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'age_restriction' => $this->faker->numberBetween(0, 18),
            'release_date' => $this->faker->date(),
            'genre' => $this->faker->randomElement(['action', 'comedy', 'drama']),
            'viewing_classification' => json_encode(['PG', 'PG-13', 'R']),
        ];
    }
}
