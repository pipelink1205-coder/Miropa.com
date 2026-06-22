<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomElement([50000, 120000, 250000, 500000, 800000]);

        return [
            'listing_id' => Listing::factory(),
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'amount' => $amount,
            'commission_amount' => round($amount * 0.05, 2),
            'status' => 'completed',
            'payment_method' => fake()->randomElement(['transferencia', 'efectivo', 'nequi', 'daviplata']),
            'shipping_method' => fake()->randomElement(['mensajero', 'entrega_personal', 'envio_nacional']),
            'completed_at' => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
