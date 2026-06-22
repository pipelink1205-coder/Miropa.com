<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Transaction $transaction): bool
    {
        if ($transaction->status !== 'completed') {
            return false;
        }

        $role = $user->id === $transaction->buyer_id ? 'buyer' : ($user->id === $transaction->seller_id ? 'seller' : null);

        if (! $role) {
            return false;
        }

        return ! Review::where('transaction_id', $transaction->id)
            ->where('reviewer_id', $user->id)
            ->exists();
    }
}
