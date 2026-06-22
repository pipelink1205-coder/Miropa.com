<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'role' => fake()->randomElement(['buyer', 'seller']),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->optional(0.8)->sentence(),
        ];
    }
}
