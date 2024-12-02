<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // Relates to User
            'name' => $this->faker->name(),
            'photo_path' => $this->faker->imageUrl(),
            'child_profile' => $this->faker->boolean(),
            'date_of_birth' => $this->faker->date(),
            'language' => $this->faker->languageCode(),
        ];
    }
}
