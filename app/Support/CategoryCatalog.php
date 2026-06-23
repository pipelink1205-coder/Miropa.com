<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryCatalog
{
    /** @return array{fashion: Collection<int, Category>, other: Collection<int, Category>} */
    public static function forPublishing(): array
    {
        $fashion = self::fashionDepartments();

        if ($fashion->isNotEmpty()) {
            $other = self::generalParents();

            return compact('fashion', 'other');
        }

        return self::legacyPublishingCatalog();
    }

    /** @return Collection<int, Category> */
    private static function fashionDepartments(): Collection
    {
        $modaRoot = Category::query()->where('slug', 'moda')->first();

        if ($modaRoot === null) {
            return collect();
        }

        return Category::query()
            ->where('parent_id', $modaRoot->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_DEPARTAMENTO)
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get(['id', 'name', 'parent_id', 'sale_mode', 'slug', 'description', 'level']);
    }

    /** @return Collection<int, Category> */
    private static function generalParents(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('vertical', 'general')
            ->where('is_active', true)
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get(['id', 'name', 'parent_id', 'sale_mode', 'slug', 'description']);
    }

    /** @return array{fashion: Collection<int, Category>, other: Collection<int, Category>} */
    private static function legacyPublishingCatalog(): array
    {
        $parents = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get(['id', 'name', 'parent_id', 'sale_mode', 'slug', 'description']);

        $fashionSlugs = config('marketplace.fashion_category_slugs', []);

        return [
            'fashion' => $parents->whereIn('slug', $fashionSlugs)->values(),
            'other' => $parents->whereNotIn('slug', $fashionSlugs)->values(),
        ];
    }
}
