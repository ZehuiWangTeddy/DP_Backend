<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-10 years', 'now');
        $endDate = (clone $startDate)->modify('+1 year');

        return [
            'user_id' => \App\Models\User::factory(), // Relates to User
            'price' => $this->faker->randomElement([7.99, 10.99, 13.99]),
            'name' => $this->faker->randomElement(['SD', 'HD', 'UHD']),
            'status' => $this->faker->randomElement(['paid', 'expired']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'payment_method' => $this->faker->randomElement([
                'PayPal',
                'Visa',
                'MasterCard',
                'American Express',
                'Discover',
                'Apple Pay',
                'Google Pay',
                'Bitcoin',
                'Ethereum',
                'Bank Transfer',
            ]),
        ];
    }
}
