<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use App\Models\User;
use App\Support\ListingFashionPayload;
use App\Support\ListingUniverses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateListingAction
{
    public function execute(User $user, array $data): Listing
    {
        return DB::transaction(function () use ($user, $data): Listing {
            $attributes = ListingFashionPayload::buildAttributes($data);

            $listing = $user->listings()->create([
                'category_id' => $data['category_id'],
                'condition_id' => $data['condition_id'],
                'location_id' => $data['location_id'] ?? null,
                'brand_id' => ListingFashionPayload::resolveBrandId($data),
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'description' => $data['description'],
                'price' => $data['price'],
                'size' => $data['size'] ?? null,
                'size_note' => $data['size_note'] ?? null,
                'color' => $data['color'] ?? null,
                'listing_mode' => $data['listing_mode'] ?? 'compra_protegida',
                'listing_type' => $data['listing_type'] ?? 'individual',
                'accepts_trade' => (bool) ($data['accepts_trade'] ?? false),
                'items_count' => ($data['listing_type'] ?? 'individual') === 'lote'
                    ? (int) ($data['items_count'] ?? 1)
                    : 1,
                'is_negotiable' => $data['is_negotiable'] ?? false,
                'currency' => $data['currency'] ?? 'COP',
                'status' => $data['status'] ?? 'draft',
                'published_at' => ($data['status'] ?? 'draft') === 'active' ? now() : null,
            ]);

            if ($attributes !== []) {
                $listing->attributes()->createMany($attributes);
            }

            ListingUniverses::sync($listing, $data['universe_ids'] ?? []);

            return $listing->fresh();
        });
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Listing::where('slug', 'like', "$slug%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
