<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->randomNumber(3),
            'icon' => fake()->randomElement(['📱', '👕', '🏠', '🚗', '⚽', '🧸', '📚', '🔧']),
            'description' => fake()->sentence(),
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
            'sale_mode' => 'marketplace',
        ];
    }
}
