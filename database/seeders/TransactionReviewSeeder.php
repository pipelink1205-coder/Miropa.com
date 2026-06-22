<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $listings = Listing::where('status', 'active')->take(10)->get();

        foreach ($listings->take(8) as $listing) {
            $buyer = $users->where('id', '!=', $listing->user_id)->random();
            $seller = $listing->user;

            $transaction = Transaction::create([
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'amount' => $listing->price,
                'commission_amount' => round($listing->price * 0.05, 2),
                'status' => 'completed',
                'payment_method' => 'transferencia',
                'shipping_method' => 'entrega_personal',
                'completed_at' => now()->subDays(rand(5, 60)),
            ]);

            // Reseña del comprador al vendedor
            Review::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $buyer->id,
                'reviewee_id' => $seller->id,
                'role' => 'buyer',
                'rating' => rand(4, 5),
                'comment' => fake()->randomElement([
                    'Excelente vendedor, muy puntual y el artículo estaba tal como lo describió.',
                    'Todo perfecto, recomendado 100%.',
                    'Buena transacción, respondió rápido y fue muy amable.',
                    'El artículo estaba en perfectas condiciones. Volveré a comprar.',
                ]),
            ]);

            // Reseña del vendedor al comprador
            Review::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $seller->id,
                'reviewee_id' => $buyer->id,
                'role' => 'seller',
                'rating' => rand(4, 5),
                'comment' => fake()->randomElement([
                    'Comprador serio y puntual. Recomendado.',
                    'Muy buena experiencia, pagó a tiempo.',
                    'Excelente comprador, sin problemas.',
                    null,
                ]),
            ]);

            // Actualizar stats del perfil del vendedor
            $profile = Profile::firstOrCreate(['user_id' => $seller->id]);
            $reviews = Review::where('reviewee_id', $seller->id)->get();
            $profile->update([
                'rating_avg' => round($reviews->avg('rating'), 2),
                'ratings_count' => $reviews->count(),
                'sales_count' => Transaction::where('seller_id', $seller->id)->where('status', 'completed')->count(),
            ]);
        }
    }
}
