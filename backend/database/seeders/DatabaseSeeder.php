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

        // Seed the series table
        Series::factory(5)->create();

        // Seed the seasons table
        Season::factory(15)->create();

        // Seed the episodes table
        Episode::factory(50)->create();

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
