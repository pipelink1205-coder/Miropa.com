<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::inRandomOrder()->first()?->id,
            'rating_avg' => fake()->randomFloat(2, 3.0, 5.0),
            'ratings_count' => fake()->numberBetween(0, 50),
            'sales_count' => fake()->numberBetween(0, 30),
            'purchases_count' => fake()->numberBetween(0, 20),
            'response_rate' => fake()->randomFloat(2, 60.00, 100.00),
            'member_since' => fake()->dateTimeBetween('-3 years', '-1 month'),
        ];
    }
}
