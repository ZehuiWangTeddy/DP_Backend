<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;
use App\Models\Season;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    protected static array $seasonQualities = [];

    public function definition(): array
    {
        $seasonId = Season::factory()->create()->season_id;

        // If quality for this season doesn't exist yet, generate it
        if (!isset(static::$seasonQualities[$seasonId])) {
            static::$seasonQualities[$seasonId] = json_encode($this->faker->randomElements(
                ['SD', 'HD', 'UHD'],
                $this->faker->numberBetween(1, 3)
            ));
        }

        return [
            'season_id' => $seasonId,
            'episode_number' => $this->faker->numberBetween(1, 20),
            'title' => $this->faker->sentence(6),
            'duration' => $this->faker->time('H:i:s'),
            'release_date' => $this->faker->date(),
            'quality' => static::$seasonQualities[$seasonId], // Use the cached quality for this season
            'available_languages' => json_encode($this->faker->randomElements(
                ['English', 'Spanish', 'French', 'German', 'Italian', 'Chinese', 'Japanese'],
                $this->faker->numberBetween(2, 4)
            )),
            'viewing_classification' => ViewingClassificationHelper::determineViewingClassification(
                $this->faker->numberBetween(0, 18)
            ),
        ];
    }
}
