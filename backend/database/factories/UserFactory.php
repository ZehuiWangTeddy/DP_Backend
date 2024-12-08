<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        // Static array to store all sent_referral_codes generated for users
        static $sentCodes = [];

        // Generate a random sent_referral_code and add it to the array
        $sentCode = fake()->regexify('[A-Za-z0-9]{10}');
        $sentCodes[] = $sentCode;

        // Get the sent_referral_code from the previous user, if any
        $previousSentCode = $sentCodes[count($sentCodes) - 2] ?? null;

        // Generate the received_referral_code, which may be the previous user's sent_referral_code or null
        $receivedCode = fake()->optional()->randomElement([$previousSentCode, null]);

        // Determine if the user gets a discount (true if received_referral_code matches sent_referral_code)
        $hasDiscount = $receivedCode === $previousSentCode;

        // Generate failed login attempts (between 0 and 5)
        $failedLoginAttempts = fake()->numberBetween(0, 5);

        // Determine if the user should be active based on failed login attempts
        $active = $failedLoginAttempts < 4;
        $trialAvailable = $active ? true : false;

        // If the user is inactive, set locked_until date within the last year
        $lockedUntil = $active ? null : fake()->dateTimeBetween('-1 year', 'now');

        // Set trial_available to false if inactive
        if (!$active) {
            $trialAvailable = false;
        }

        // Return the generated user attributes
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'), // Default hashed password
            'address' => fake()->address(),
            'failed_login_attempts' => $failedLoginAttempts, // Randomly generated failed login attempts
            'active' => $active, // Set to false if failed_login_attempts >= 4
            'sent_referral_code' => $sentCode, // The generated sent_referral_code
            'received_referral_code' => $receivedCode, // The received_referral_code that matches the previous sent_referral_code (if any)
            'has_discount' => $hasDiscount, // Set to true if received_referral_code matches sent_referral_code
            'locked_until' => $lockedUntil, // Set locked_until if inactive
            'trial_available' => $trialAvailable, // If inactive, trial_available is false
            'user_role' => fake()->boolean(), // Assuming a binary role (admin/user)
            'password_reset_token' => fake()->optional()->randomElement([Str::random(60), null]), // Random token or null
            'password_reset_token_expiry' => fake()->optional()->randomElement([now()->addHours(2), null]), // Expiry time for the reset token
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
