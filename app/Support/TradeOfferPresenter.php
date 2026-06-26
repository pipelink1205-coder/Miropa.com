<?php

namespace App\Support;

use App\Http\Resources\TradeOfferResource;
use App\Models\Listing;
use App\Models\TradeOffer;
use Illuminate\Http\Request;

class TradeOfferPresenter
{
    /** @return array<string, mixed> */
    public static function forInertia(TradeOffer $offer): array
    {
        $offer->loadMissing([
            'proposer',
            'targetListing.primaryImage',
            'targetListing.condition',
            'offeredListing.primaryImage',
            'offeredListing.condition',
        ]);

        return TradeOfferResource::make($offer)->toArray(Request::create('/trueques', 'GET'));
    }

    /** @return array<string, mixed> */
    public static function listingOption(Listing $listing): array
    {
        $listing->loadMissing(['primaryImage', 'condition']);

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'price_formatted' => $listing->price_formatted,
            'status' => $listing->status,
            'condition' => $listing->condition?->name,
            'primary_image' => $listing->primaryImage
                ? asset('storage/'.$listing->primaryImage->path)
                : null,
        ];
    }
}
