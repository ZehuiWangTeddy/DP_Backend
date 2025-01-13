<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;
use App\Models\Series;
use App\Models\Season;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Series>
 */
class SeriesFactory extends Factory
{
    public function definition(): array
    {
        $age = $this->faker->randomElement([0, 6, 9, 12, 16, 18]);
        
        return [
            'title' => $this->faker->sentence(3),
            'age_restriction' => $age,
            'release_date' => $this->faker->dateTimeBetween('-30 years', 'now')->format('Y-m-d'),
            'genre' => $this->faker->randomElement([
                'Action',
                'Comedy',
                'Drama',
                'Thriller',
                'Horror',
                'Fantasy',
                'Science Fiction',
                'Romance',
                'Documentary',
            ]),
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification($age),
        ];
    }

    public function withSeasons(int $count = 1)
    {
        return $this->afterCreating(function (Series $series) use ($count) {
            Season::factory()
                ->count($count)
                ->sequence(fn ($sequence) => ['season_number' => $sequence->index + 1])
                ->for($series)
                ->create();
        });
    }
}
