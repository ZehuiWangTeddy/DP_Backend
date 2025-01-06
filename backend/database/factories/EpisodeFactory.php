<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    public function definition(): array
    {
        // Generate the age restriction first
        $age = $this->faker->randomElement([0, 6, 9, 12, 16, 18]);

        return [
            'season_id' => \App\Models\Season::factory(), // Relates to Season
            'episode_number' => $this->faker->numberBetween(1, 20), // Unique episode number within a season
            'title' => $this->faker->sentence(6),
            'duration' => $this->faker->time('H:i:s'),
            'release_date' => $this->faker->date(),
            'quality' => json_encode($this->faker->randomElements(
                ['SD', 'HD', 'UHD'],
                $this->faker->numberBetween(1, 3) // Choose between 1 and 3 qualities
            )),
            'available_languages' => json_encode($this->faker->randomElements(
                ['English', 'Spanish', 'French', 'German', 'Italian', 'Chinese', 'Japanese'],
                $this->faker->numberBetween(2, 4)
            )),
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification($age), // Viewing classification based on age
        ];
    }
}
