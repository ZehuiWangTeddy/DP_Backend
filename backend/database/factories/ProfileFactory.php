<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Correctly links to a User factory
            'name' => $this->faker->name(),
            'photo_path' => $this->faker->imageUrl(),
            'child_profile' => $this->faker->boolean(),
            'date_of_birth' => $this->faker->date(),
            'language' => $this->faker->languageCode(),
        ];
    }
}

