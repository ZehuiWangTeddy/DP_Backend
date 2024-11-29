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
            'content_type' => $this->faker->randomElement([
                '18+',
                'For Kids',
                'Includes Violence',
                'Includes Sex',
                'Family Friendly',
                'Educational',
                'Sci-Fi Themes',
                'Fantasy Elements',
            ]), // Types of content
            'genre' => json_encode($this->faker->randomElements([
                'Action',
                'Comedy',
                'Drama',
                'Horror',
                'Thriller',
                'Fantasy',
                'Science Fiction',
                'Romance',
                'Documentary',
                'Animation',
                'Crime',
                'Mystery',
                'Adventure',
                'Western',
                'Biographical',
            ], $this->faker->numberBetween(1, 3))), // JSON array for multiple genres
            'minimum_age' => $this->faker->randomElement([0, 6, 9, 12, 16, 18]),
        ];
    }
}
