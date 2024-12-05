<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subtitle;
use App\Models\Movie;
use App\Models\Episode;

/**
 * @extends Factory<Subtitle>
 */
class SubtitleFactory extends Factory
{
    protected $model = Subtitle::class;

    public function definition(): array
    {
        return [
            'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it']),
            'movie_id' => Movie::inRandomOrder()->first()?->id ?? Movie::factory(),
            'episode_id' => Episode::inRandomOrder()->first()?->id ?? Episode::factory(),
            'subtitle_path' => $this->faker->filePath(),
        ];
    }
}

