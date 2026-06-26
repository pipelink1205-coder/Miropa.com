<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\TradeOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TradeOffer>
 */
class TradeOfferFactory extends Factory
{
    protected $model = TradeOffer::class;

    public function definition(): array
    {
        return [
            'proposer_id' => User::factory(),
            'target_listing_id' => Listing::factory(),
            'offered_listing_id' => Listing::factory(),
            'conversation_id' => null,
            'status' => 'pending',
            'message' => fake()->optional()->sentence(),
            'responded_at' => null,
            'completed_at' => null,
        ];
    }
}
