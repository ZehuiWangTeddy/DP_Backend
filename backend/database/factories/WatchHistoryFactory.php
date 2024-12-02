<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WatchHistory>
 */
class WatchHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'profile_id' => \App\Models\Profile::factory(),
            'episode_id' => $this->faker->randomElement([
                \App\Models\Episode::factory(),
                null // Allow null for optional fields
            ]),
            'movie_id' => $this->faker->randomElement([
                \App\Models\Movie::factory(),
                null // Allow null for optional fields
            ]),
            'resume_to' => $this->faker->time('H:i:s'), // Quit time format
            'times_watched' => $this->faker->numberBetween(1, 10), // Random number for times watched
            'watched_time_stamp' => $this->faker->dateTime(), // Random watched date
            'viewing_status' => $this->faker->randomElement(['paused', 'finished']),
        ];
    }
}
