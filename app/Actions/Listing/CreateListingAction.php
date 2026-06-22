<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateListingAction
{
    public function execute(User $user, array $data): Listing
    {
        return DB::transaction(function () use ($user, $data): Listing {
            $listing = $user->listings()->create([
                'category_id' => $data['category_id'],
                'condition_id' => $data['condition_id'],
                'location_id' => $data['location_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'description' => $data['description'],
                'price' => $data['price'],
                'is_negotiable' => $data['is_negotiable'] ?? false,
                'currency' => $data['currency'] ?? 'COP',
                'status' => $data['status'] ?? 'draft',
                'published_at' => ($data['status'] ?? 'draft') === 'active' ? now() : null,
            ]);

            if (! empty($data['attributes'])) {
                $attrs = collect($data['attributes'])->map(fn ($a) => [
                    'attribute_key' => $a['key'],
                    'attribute_value' => $a['value'],
                ]);
                $listing->attributes()->createMany($attrs->toArray());
            }

            return $listing;
        });
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Listing::where('slug', 'like', "$slug%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
