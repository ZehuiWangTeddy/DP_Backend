<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'season_number' => 1, // Default value, will be overridden by sequence
            'release_date' => function (array $attributes) {
                $series = Series::find($attributes['series_id']);
                return $this->faker->dateTimeBetween(
                    $series->release_date,
                    '+5 years'
                )->format('Y-m-d');
            },
        ];
    }

    // Add new state methods
    public function withEpisodes(int $count = 10)
    {
        return $this->afterCreating(function (Season $season) use ($count) {
            Episode::factory()
                ->count($count)
                ->sequence(fn ($sequence) => ['episode_number' => $sequence->index + 1])
                ->for($season)
                ->create();
        });
    }
}
