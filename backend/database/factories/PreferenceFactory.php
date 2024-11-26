<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Preference>
 */
class PreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'profile_id' => \App\Models\Profile::factory(), // Relates to Profile
            'content_type' => json_encode($this->faker->randomElements(['movie', 'series'], 2)),
            'genre' => json_encode($this->faker->randomElements(['action', 'comedy', 'drama'], 3)),
            'minimum_age' => $this->faker->numberBetween(0, 18),
        ];
    }
}
