<?php

namespace App\Support;

use App\Models\Listing;
use App\Models\TradeOffer;
use App\Models\User;
use App\Services\TradeEligibilityService;

class ListingTradeContext
{
    public function __construct(private TradeEligibilityService $eligibility) {}

    /** @return array<string, mixed> */
    public function forShow(Listing $listing, ?User $user): array
    {
        if (! config('marketplace.trade.enabled', true)) {
            return ['enabled' => false];
        }

        $context = [
            'enabled' => true,
            'accepts_trade' => (bool) $listing->accepts_trade,
            'can_propose' => false,
            'can_enable_on_listing' => false,
            'ineligible_reason' => null,
            'my_active_listings' => [],
            'pending_offer' => null,
        ];

        if ($user === null) {
            return $context;
        }

        $reason = $this->eligibility->failureReason($user);
        $isOwner = $listing->user_id === $user->id;

        if ($isOwner) {
            $context['can_enable_on_listing'] = $reason === null;

            return $context;
        }

        if ($reason !== null) {
            $context['ineligible_reason'] = $reason;

            return $context;
        }

        if ($listing->status !== 'active' || ! $listing->accepts_trade) {
            return $context;
        }

        if (($listing->category->sale_mode ?? 'marketplace') !== 'marketplace') {
            return $context;
        }

        $pendingOffer = TradeOffer::query()
            ->where('proposer_id', $user->id)
            ->where('target_listing_id', $listing->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with('offeredListing.primaryImage')
            ->first();

        if ($pendingOffer !== null) {
            $context['pending_offer'] = TradeOfferPresenter::forInertia($pendingOffer);

            return $context;
        }

        $myListings = Listing::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('id', '!=', $listing->id)
            ->with(['primaryImage', 'condition'])
            ->latest()
            ->get()
            ->map(fn (Listing $item) => TradeOfferPresenter::listingOption($item))
            ->values()
            ->all();

        if ($myListings === []) {
            $context['ineligible_reason'] = 'Publica al menos un anuncio activo para ofrecer en trueque.';

            return $context;
        }

        $context['can_propose'] = true;
        $context['my_active_listings'] = $myListings;

        return $context;
    }

    /** @return array<string, mixed> */
    public function publishMeta(User $user): array
    {
        return [
            'enabled' => (bool) config('marketplace.trade.enabled', true),
            'eligible' => $this->eligibility->isEligible($user),
            'ineligible_reason' => $this->eligibility->failureReason($user),
            'eligible_condition_slugs' => FashionConditions::tradeEligibleSlugs(),
        ];
    }
}
