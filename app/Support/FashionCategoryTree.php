<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;

class FashionCategoryTree
{
    public static function departmentOrFail(string $shortSlug): Category
    {
        return Category::query()
            ->where('slug', 'moda-'.$shortSlug)
            ->where('level', FashionCategoryDefinitions::LEVEL_DEPARTAMENTO)
            ->where('vertical', FashionCategoryDefinitions::VERTICAL)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /** @return list<int> */
    public static function descendantIds(Category $category): array
    {
        $ids = [$category->id];
        $children = Category::query()
            ->where('parent_id', $category->id)
            ->where('is_active', true)
            ->get(['id']);

        foreach ($children as $child) {
            $ids = array_merge($ids, self::descendantIds($child));
        }

        return array_values(array_unique($ids));
    }

    /**
     * Segmentos Niños (Bebé / Niña / Niño). Vacío en otros departamentos.
     *
     * @return list<array{id: int, name: string, slug: string, value: string}>
     */
    public static function segmentChips(Category $department): array
    {
        if ($department->slug !== 'moda-ninos') {
            return [];
        }

        return Category::query()
            ->where('parent_id', $department->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_SEGMENTO)
            ->orderBy('position')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'value' => str($c->slug)->after('moda-ninos-')->toString(),
            ])
            ->values()
            ->all();
    }

    /**
     * Chips de categoría (Ropa, Calzado, …). En Niños requiere segmento seleccionado.
     *
     * @return list<array{id: int, name: string, slug: string, level: string|null}>
     */
    public static function navigationChips(Category $department, ?Category $segment = null): array
    {
        if ($department->slug === 'moda-ninos') {
            if ($segment === null) {
                return [];
            }

            return Category::query()
                ->where('parent_id', $segment->id)
                ->where('level', FashionCategoryDefinitions::LEVEL_CATEGORIA)
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'level'])
                ->map(fn (Category $c) => $c->only(['id', 'name', 'slug', 'level']))
                ->values()
                ->all();
        }

        return Category::query()
            ->where('parent_id', $department->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_CATEGORIA)
            ->orderBy('position')
            ->get(['id', 'name', 'slug', 'level'])
            ->map(fn (Category $c) => $c->only(['id', 'name', 'slug', 'level']))
            ->values()
            ->all();
    }

    /**
     * Subcategorías (tipos) bajo una categoría seleccionada.
     *
     * @return list<array{id: int, name: string, slug: string}>
     */
    public static function tiposForCategoria(?Category $categoria): array
    {
        if ($categoria === null) {
            return [];
        }

        return Category::query()
            ->where('parent_id', $categoria->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_TIPO)
            ->orderBy('position')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $c) => $c->only(['id', 'name', 'slug']))
            ->values()
            ->all();
    }

    /** @return Collection<int, Category> */
    public static function activeDepartments(): Collection
    {
        return Category::query()
            ->where('vertical', FashionCategoryDefinitions::VERTICAL)
            ->where('level', FashionCategoryDefinitions::LEVEL_DEPARTAMENTO)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    public static function isFashionCategory(Category $category): bool
    {
        if ($category->vertical === FashionCategoryDefinitions::VERTICAL) {
            return true;
        }

        return str_starts_with($category->slug, 'moda-');
    }
}
