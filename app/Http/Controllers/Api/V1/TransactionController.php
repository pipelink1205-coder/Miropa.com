<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionStatusRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Listing;
use App\Models\Profile;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $listing = Listing::findOrFail($request->listing_id);

        if ($listing->user_id === $request->user()->id) {
            return response()->json(['message' => 'No puedes comprar tu propio anuncio.'], 422);
        }

        if ($listing->status !== 'active') {
            return response()->json(['message' => 'Este anuncio no está disponible.'], 422);
        }

        $transaction = DB::transaction(function () use ($request, $listing) {
            $transaction = Transaction::create([
                'listing_id' => $listing->id,
                'buyer_id' => $request->user()->id,
                'seller_id' => $listing->user_id,
                'amount' => $listing->price,
                'commission_amount' => round($listing->price * 0.05, 2),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_method' => $request->shipping_method ?? 'in_person',
            ]);

            $listing->update(['status' => 'reserved']);

            return $transaction;
        });

        return response()->json([
            'data' => new TransactionResource($transaction->load(['listing', 'buyer', 'seller'])),
        ], 201);
    }

    public function updateStatus(UpdateTransactionStatusRequest $request, Transaction $transaction): JsonResponse
    {
        $newStatus = $request->status;

        $allowedTransitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => ['completed'],
        ];

        $current = $transaction->status;

        if (! isset($allowedTransitions[$current]) || ! in_array($newStatus, $allowedTransitions[$current])) {
            return response()->json([
                'message' => "No se puede cambiar el estado de '$current' a '$newStatus'.",
            ], 422);
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            $transaction->listing->update(['status' => 'sold', 'sold_at' => now()]);

            Profile::where('user_id', $transaction->seller_id)->increment('sales_count');
            Profile::where('user_id', $transaction->buyer_id)->increment('purchases_count');
        }

        if ($newStatus === 'cancelled') {
            $transaction->listing->update(['status' => 'active']);
        }

        $transaction->update($updates);

        return response()->json([
            'data' => new TransactionResource($transaction->fresh()->load(['listing', 'buyer', 'seller'])),
        ]);
    }
}
