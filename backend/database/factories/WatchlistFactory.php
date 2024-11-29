<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Profile;
use App\Models\Episode;
use App\Models\Movie;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Watchlist>
 */
class WatchlistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(), // Create related Profile
            'episode_id' => $this->faker->boolean(50) ? Episode::factory() : null, // Randomly assign an Episode
            'movie_id' => $this->faker->boolean(50) ? Movie::factory() : null, // Randomly assign a Movie
            'viewing_status' => $this->faker->randomElement(['to_watch', 'paused', 'finished']), // Random viewing status
        ];
    }
}
