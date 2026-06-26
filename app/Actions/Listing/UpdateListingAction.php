<?php

namespace App\Actions\Listing;

use App\Models\Category;
use App\Models\Listing;
use App\Support\FashionListingRules;
use App\Support\ListingFashionPayload;
use App\Support\ListingUniverses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateListingAction
{
    public function execute(Listing $listing, array $data): Listing
    {
        return DB::transaction(function () use ($listing, $data): Listing {
            $listing->loadMissing('category');

            $updates = array_filter([
                'category_id' => $data['category_id'] ?? null,
                'condition_id' => $data['condition_id'] ?? null,
                'location_id' => array_key_exists('location_id', $data) ? $data['location_id'] : null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? null,
                'is_negotiable' => $data['is_negotiable'] ?? null,
                'status' => $data['status'] ?? null,
                'accepts_trade' => array_key_exists('accepts_trade', $data)
                    ? (bool) $data['accepts_trade']
                    : null,
            ], fn ($v) => ! is_null($v));

            if (isset($data['title']) && $data['title'] !== $listing->title) {
                $updates['slug'] = $this->uniqueSlug($data['title'], $listing->id);
            }

            if (isset($data['status']) && $data['status'] === 'active' && ! $listing->published_at) {
                $updates['published_at'] = now();
            }

            $categoryId = $data['category_id'] ?? $listing->category_id;
            $category = $listing->category;
            if ($categoryId !== $listing->category_id) {
                $category = Category::query()->find($categoryId);
            }

            if ($category && FashionListingRules::requiresPublishAttributes($category)) {
                $updates['brand_id'] = ListingFashionPayload::resolveBrandId($data);
                $updates['size'] = $data['size'] ?? null;
                $updates['size_note'] = $data['size_note'] ?? null;
                $updates['color'] = $data['color'] ?? null;
                $updates['listing_mode'] = $data['listing_mode'] ?? $listing->listing_mode;
                $updates['listing_type'] = $data['listing_type'] ?? $listing->listing_type;
                $updates['items_count'] = ($data['listing_type'] ?? $listing->listing_type) === 'lote'
                    ? (int) ($data['items_count'] ?? $listing->items_count ?? 1)
                    : 1;
            }

            if (! empty($updates)) {
                $listing->update($updates);
            }

            if ($category && FashionListingRules::requiresPublishAttributes($category)) {
                $attributes = ListingFashionPayload::buildAttributes($data);
                $listing->attributes()->delete();
                if ($attributes !== []) {
                    $listing->attributes()->createMany($attributes);
                }
            }

            if (array_key_exists('universe_ids', $data)) {
                ListingUniverses::sync($listing->fresh(), $data['universe_ids'] ?? []);
            }

            return $listing->fresh();
        });
    }

    private function uniqueSlug(string $title, int $ignoreId): string
    {
        $slug = Str::slug($title);
        $count = Listing::query()
            ->where('id', '!=', $ignoreId)
            ->where('slug', 'like', "{$slug}%")
            ->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
