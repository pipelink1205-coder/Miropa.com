<?php

namespace Database\Factories;

use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Condition>
 */
class ConditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Como nuevo', 'Buen estado', 'Con detalles']),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
        ];
    }
}
