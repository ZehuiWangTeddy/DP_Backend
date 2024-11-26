<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'duration' => $this->faker->time('H:i:s'),
            'release_date' => $this->faker->date(),
            'quality' => json_encode(['HD', 'SD', '4K']),
            'age_restriction' => $this->faker->numberBetween(0, 18),
            'genre' => $this->faker->randomElement(['action', 'comedy', 'drama']),
            'viewing_classification' => json_encode(['PG', 'PG-13', 'R']),
            'available_languages' => json_encode(['English', 'Spanish', 'French']),
        ];
    }
}
