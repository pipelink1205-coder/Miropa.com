<?php

namespace App\Policies;

use App\Models\TradeOffer;
use App\Models\User;

class TradeOfferPolicy
{
    public function view(User $user, TradeOffer $tradeOffer): bool
    {
        return $user->id === $tradeOffer->proposer_id
            || $user->id === $tradeOffer->targetListing->user_id;
    }

    public function respond(User $user, TradeOffer $tradeOffer): bool
    {
        return $user->id === $tradeOffer->targetListing->user_id;
    }

    public function cancel(User $user, TradeOffer $tradeOffer): bool
    {
        return $user->id === $tradeOffer->proposer_id;
    }

    public function complete(User $user, TradeOffer $tradeOffer): bool
    {
        return $user->id === $tradeOffer->proposer_id
            || $user->id === $tradeOffer->targetListing->user_id;
    }
}
