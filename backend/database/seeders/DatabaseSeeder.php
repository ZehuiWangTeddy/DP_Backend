<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Preference;
use App\Models\Subscription;
use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Watchlist;
use App\Models\WatchHistory;
use App\Models\Subtitle;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the users table
        User::factory(10)->create();

        // Seed the profiles table
        Profile::factory(10)->create();

        // Seed the preferences table
        Preference::factory(10)->create();

        // Seed the subscriptions table
        Subscription::factory(10)->create();

        // Create series with their seasons and episodes
        Series::factory(5)
            ->has(
                Season::factory()
                    ->count(3) // 3 seasons per series
                    ->sequence(fn ($sequence) => ['season_number' => $sequence->index + 1])
                    ->has(
                        Episode::factory()
                            ->count(10) // 10 episodes per season
                            ->sequence(fn ($sequence) => ['episode_number' => $sequence->index + 1])
                    )
            )
            ->create();

        // Seed the movies table
        Movie::factory(20)->create();

        // Seed the watchlist table
        Watchlist::factory(30)->create();

        // Seed the watch history table
        WatchHistory::factory(40)->create();

        // Seed the subtitles table
        Subtitle::factory(50)->create(); // Adding Subtitle seeding

        // Add a specific user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
