<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Series>
 */
class SeriesFactory extends Factory
{
    public function definition(): array
    {
        // Generate the age restriction first
        $age = $this->faker->randomElement([0, 6, 9, 12, 16, 18]);

        return [
            'title' => $this->faker->sentence(6), // Random series title
            'age_restriction' => $age, // Age restriction for the series
            'release_date' => $this->faker->date(), // Series release date
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
            ]), // Expanded genre list
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification($age), // Viewing classification based on age
        ];
    }
}
