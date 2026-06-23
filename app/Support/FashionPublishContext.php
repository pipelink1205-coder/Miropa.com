<?php

namespace App\Support;

use App\Models\Category;

class FashionPublishContext
{
    /** @return array<string, mixed> */
    public static function forCategory(Category $category): array
    {
        if (! FashionCategoryTree::isFashionCategory($category)) {
            return ['is_fashion' => false];
        }

        $contextKey = self::resolveContextKey($category);
        $base = config("fashion_publish.contexts.{$contextKey}", config('fashion_publish.contexts.ropa'));

        return [
            'is_fashion' => true,
            'key' => $contextKey,
            'sizes' => self::sizesForContext($base),
            'height_sizes' => ($base['show_size_note'] ?? false) ? config('fashion_sizes.altura_cm', []) : [],
            'default_size' => $base['default_size'] ?? null,
            'show_brand' => (bool) ($base['show_brand'] ?? true),
            'brand_required' => (bool) ($base['brand_required'] ?? false),
            'show_color' => (bool) ($base['show_color'] ?? true),
            'show_measurements' => (bool) ($base['show_measurements'] ?? false),
            'show_sole_length' => (bool) ($base['show_sole_length'] ?? false),
            'show_size_mismatch' => (bool) ($base['show_size_mismatch'] ?? false),
            'show_size_note' => (bool) ($base['show_size_note'] ?? false),
            'show_listing_type' => (bool) ($base['show_listing_type'] ?? false),
            'photo_tips' => $base['photo_tips'] ?? [],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public static function leafContextMap(): array
    {
        $leaves = Category::query()
            ->moda()
            ->where('level', FashionCategoryDefinitions::LEVEL_TIPO)
            ->where('is_active', true)
            ->get(['id', 'slug', 'name', 'parent_id', 'level']);

        return $leaves->mapWithKeys(function (Category $leaf) {
            return [$leaf->id => self::forCategory($leaf)];
        })->all();
    }

    /** @return list<string> */
    public static function allowedColorNames(): array
    {
        return collect(config('fashion_colors', []))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    public static function resolveContextKey(Category $category): string
    {
        $chain = self::ancestorChain($category);
        $categoria = collect($chain)->firstWhere('level', FashionCategoryDefinitions::LEVEL_CATEGORIA);
        $segment = collect($chain)->firstWhere('level', FashionCategoryDefinitions::LEVEL_SEGMENTO);

        $categoriaSlug = $categoria?->slug ?? '';

        if (str_contains($categoriaSlug, '-calzado')) {
            return 'calzado';
        }

        if (str_contains($categoriaSlug, '-accesorios') || str_contains($categoriaSlug, '-bolsos')) {
            return 'accesorios';
        }

        if ($segment !== null) {
            return $segment->slug === 'moda-ninos-bebe' ? 'ninos_bebe' : 'ninos_nina_nino';
        }

        return 'ropa';
    }

    /** @param  array<string, mixed>  $contextConfig */
    private static function sizesForContext(array $contextConfig): array
    {
        $sizes = [];
        foreach ($contextConfig['size_keys'] ?? [] as $key) {
            $sizes = array_merge($sizes, config("fashion_sizes.{$key}", []));
        }

        if ($sizes === [] && isset($contextConfig['default_size'])) {
            return [$contextConfig['default_size']];
        }

        $extras = config('fashion_publish.size_extras', []);

        return array_values(array_unique([...$sizes, ...$extras]));
    }

    /** @return list<Category> */
    private static function ancestorChain(Category $category): array
    {
        $chain = [$category];
        $current = $category;

        while ($current->parent_id !== null) {
            $current = $current->parent()->first(['id', 'slug', 'name', 'parent_id', 'level']);
            if ($current === null) {
                break;
            }
            array_unshift($chain, $current);
        }

        return $chain;
    }
}
