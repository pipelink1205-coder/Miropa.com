<?php

namespace App\Actions\Listing;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateListingAction
{
    public function execute(User $user, array $data): Listing
    {
        return DB::transaction(function () use ($user, $data): Listing {
            $attributes = $this->buildAttributes($data);

            $listing = $user->listings()->create([
                'category_id' => $data['category_id'],
                'condition_id' => $data['condition_id'],
                'location_id' => $data['location_id'] ?? null,
                'brand_id' => $this->resolveBrandId($data),
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'description' => $data['description'],
                'price' => $data['price'],
                'size' => $data['size'] ?? null,
                'size_note' => $data['size_note'] ?? null,
                'color' => $data['color'] ?? null,
                'listing_mode' => $data['listing_mode'] ?? 'compra_protegida',
                'listing_type' => $data['listing_type'] ?? 'individual',
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

            return $listing;
        });
    }

    /** @return list<array{attribute_key: string, attribute_value: string}> */
    private function buildAttributes(array $data): array
    {
        $attributes = [];

        if (! empty($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attr) {
                if (! empty($attr['key']) && isset($attr['value']) && $attr['value'] !== '') {
                    $attributes[] = [
                        'attribute_key' => $attr['key'],
                        'attribute_value' => (string) $attr['value'],
                    ];
                }
            }
        }

        if (! empty($data['size_label'])) {
            $attributes[] = ['attribute_key' => 'size_label', 'attribute_value' => $data['size_label']];
        }

        if (! empty($data['size_fits_as'])) {
            $attributes[] = ['attribute_key' => 'size_fits_as', 'attribute_value' => $data['size_fits_as']];
        }

        if (! empty($data['measurements']) && is_array($data['measurements'])) {
            foreach ($data['measurements'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $attributes[] = [
                        'attribute_key' => (string) $key,
                        'attribute_value' => (string) $value,
                    ];
                }
            }
        }

        return $attributes;
    }

    private function resolveBrandId(array $data): ?int
    {
        if (! empty($data['brand_id'])) {
            return (int) $data['brand_id'];
        }

        if (empty($data['brand_name'])) {
            return null;
        }

        $name = trim($data['brand_name']);
        $slug = Str::slug($name);

        if ($slug === '') {
            return null;
        }

        return Brand::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_active' => true],
        )->id;
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = Listing::where('slug', 'like', "$slug%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
}
