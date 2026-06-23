<?php

namespace App\Support;

use App\Models\Category;
use App\Models\ImpactFactor;
use App\Models\Listing;

class SecondLifeImpact
{
    /** @return array{water_liters: float, co2_kg: float, label: string}|null */
    public static function forListing(Listing $listing): ?array
    {
        if (! $listing->relationLoaded('category')) {
            $listing->load('category');
        }

        $category = $listing->category;
        if ($category === null || ! FashionCategoryTree::isFashionCategory($category)) {
            return null;
        }

        $productType = self::resolveProductType($category);
        $factor = ImpactFactor::query()->where('product_type', $productType)->first()
            ?? ImpactFactor::query()->where('product_type', 'default')->first();

        if ($factor === null) {
            return null;
        }

        return [
            'water_liters' => (float) $factor->water_liters,
            'co2_kg' => (float) $factor->co2_kg,
            'label' => self::formatLabel((float) $factor->water_liters, (float) $factor->co2_kg),
        ];
    }

    private static function resolveProductType(Category $category): string
    {
        $slug = $category->slug;

        if (str_contains($slug, 'calzado') || str_contains($slug, '-calzado-')) {
            return 'calzado';
        }

        if (str_contains($slug, 'bolsos') || str_contains($slug, '-bolsos-')) {
            return 'bolso';
        }

        if (str_contains($slug, 'accesorios')) {
            return 'accesorio';
        }

        return 'prenda';
    }

    private static function formatLabel(float $water, float $co2): string
    {
        return sprintf('Ahorraste ~%.0f L de agua y %.1f kg CO₂', $water, $co2);
    }
}
