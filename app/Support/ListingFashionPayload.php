<?php

namespace App\Support;

use App\Models\Brand;
use Illuminate\Support\Str;

class ListingFashionPayload
{
    /** @return list<array{attribute_key: string, attribute_value: string}> */
    public static function buildAttributes(array $data): array
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

    public static function resolveBrandId(array $data): ?int
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

    /** @return array<string, string> */
    public static function attributesFromListing(\App\Models\Listing $listing): array
    {
        if (! $listing->relationLoaded('attributes')) {
            $listing->load('attributes');
        }

        return $listing->attributes
            ->pluck('attribute_value', 'attribute_key')
            ->all();
    }
}
