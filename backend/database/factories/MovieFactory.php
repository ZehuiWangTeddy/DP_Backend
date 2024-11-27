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
            'quality' => $this->faker->randomElement(['SD', 'HD', 'UHD']), // Movie quality
            'age_restriction' => $age, // Age restriction for the movie
            'genre' => $this->faker->randomElement([
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
                'Adventure',
            ]), // Movie genre
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification($age), // Viewing classification based on age
            'available_languages' => json_encode($this->faker->randomElements(
                ['English', 'Spanish', 'French', 'German', 'Italian', 'Chinese', 'Japanese'],
                $this->faker->numberBetween(2, 4)
            )), // Available languages
        ];
    }
}
