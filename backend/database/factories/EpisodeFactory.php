<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\ViewingClassificationHelper;
use App\Models\Season;
use DateTime;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    protected static array $seasonQualities = [];

    public function definition(): array
    {
        return [
            'episode_number' => 1, // Default value, will be overridden by sequence
            'title' => $this->faker->sentence(3),
            'duration' => $this->faker->dateTimeBetween('00:20:00', '01:00:00')->format('H:i:s'),
            'release_date' => function (array $attributes) {
                $season = Season::find($attributes['season_id']);
                return $this->faker->dateTimeBetween(
                    $season->release_date,
                    (new DateTime($season->release_date))->modify('+1 year')->format('Y-m-d')
                )->format('Y-m-d');
            },
            'quality' => function (array $attributes) {
                return static::$seasonQualities[$attributes['season_id']] ?? '1080p';
            },
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
