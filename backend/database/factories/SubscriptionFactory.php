<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // Relates to User
            'price' => $this->faker->randomFloat(2, 5.99, 15.99),
            'name' => $this->faker->randomElement(['SD', 'HD', 'UHD']),
            'status' => $this->faker->randomElement(['paid', 'expired']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'payment_method' => $this->faker->randomElement(['PayPal', 'Visa', 'MasterCard']),
        ];
    }
}
