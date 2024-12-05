<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Store a default hashed password for reuse.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'), // Default hashed password
            'address' => fake()->address(),
            'failed_login_attempts' => fake()->numberBetween(0, 5),
            'active' => fake()->boolean(),
            'sent_referral_code' => fake()->regexify('[A-Za-z0-9]{10}'),
            'received_referral_code' => fake()->regexify('[A-Za-z0-9]{10}'),
            'has_discount' => fake()->boolean(),
            'locked_until' => fake()->optional()->dateTime(),
            'trial_available' => fake()->boolean(),
            'user_role' => fake()->boolean(), // Assuming binary role (admin/user)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
