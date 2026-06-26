<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4, false);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'condition_id' => Condition::inRandomOrder()->first()?->id ?? 1,
            'location_id' => Location::inRandomOrder()->first()?->id,
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->randomNumber(5),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomElement([
                50000, 80000, 120000, 200000, 350000, 500000, 750000, 1000000, 1500000,
            ]),
            'is_negotiable' => fake()->boolean(40),
            'currency' => 'COP',
            'status' => 'active',
            'accepts_trade' => false,
            'views_count' => fake()->numberBetween(0, 500),
            'favorites_count' => fake()->numberBetween(0, 50),
            'published_at' => fake()->dateTimeBetween('-6 months'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }

    public function sold(): static
    {
        return $this->state(fn () => [
            'status' => 'sold',
            'sold_at' => fake()->dateTimeBetween('-3 months'),
        ]);
    }

    public function acceptsTrade(): static
    {
        return $this->state(function (): array {
            $condition = Condition::query()
                ->where('slug', 'nuevo-con-etiqueta')
                ->first();

            if ($condition === null) {
                $condition = Condition::factory()->create([
                    'name' => 'Nuevo con etiqueta',
                    'slug' => 'nuevo-con-etiqueta',
                ]);
            }

            return [
                'accepts_trade' => true,
                'condition_id' => $condition->id,
            ];
        });
    }
}
