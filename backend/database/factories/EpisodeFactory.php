<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'season_id' => \App\Models\Season::factory(), // Relates to Season
            'episode_number' => $this->faker->numberBetween(1, 20),
            'title' => $this->faker->sentence(),
            'quality' => json_encode(['HD', 'SD', '4K']),
            'duration' => $this->faker->time('H:i:s'),
            'available_languages' => json_encode(['English', 'Spanish', 'French']),
            'release_date' => $this->faker->date(),
            'viewing_classification' => json_encode(['PG', 'PG-13', 'R']),
        ];
    }
}
