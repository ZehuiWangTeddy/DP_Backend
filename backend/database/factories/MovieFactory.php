<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        // Generate the age restriction first
        $age = $this->faker->randomElement([0, 6, 9, 12, 16, 18]);

        return [
            'title' => $this->faker->sentence(3), // Generates a random movie title
            'duration' => $this->faker->time('H:i:s'), // Movie duration in H:i:s format
            'release_date' => $this->faker->date(), // Movie release date
            'quality' => json_encode($this->faker->randomElements(
                ['SD', 'HD', 'UHD'],
                $this->faker->numberBetween(1, 3) // Choose between 1 and 3 qualities
            )),
            'age_restriction' => $age, // Age restriction for the movie
            'genre' => json_encode($this->faker->randomElements(
                [
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
                ],
                $this->faker->numberBetween(1, 3) // Select 1 to 3 genres
            )),
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification(
                $this->faker->numberBetween(0, 18)
            ), // Viewing classification based on age
            'available_languages' => json_encode($this->faker->randomElements(
                ['English', 'Spanish', 'French', 'German', 'Italian', 'Chinese', 'Japanese'],
                $this->faker->numberBetween(2, 4)
            )), // Available languages
        ];
    }
}
