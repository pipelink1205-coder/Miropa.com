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
            'name' => fake()->randomElement(['Nuevo', 'Como nuevo', 'Buen estado', 'Aceptable']),
            'description' => fake()->sentence(),
        ];
    }
}
