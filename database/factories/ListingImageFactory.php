<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingImage>
 */
class ListingImageFactory extends Factory
{
    protected $model = ListingImage::class;

    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'path' => 'listings/'.fake()->uuid().'.jpg',
            'position' => fake()->numberBetween(1, 8),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'position' => 1]);
    }
}
