<?php

namespace App\Support;

use App\Models\Category;

class FashionPublishCatalog
{
    /** @return list<array<string, mixed>> */
    public static function pickerTree(): array
    {
        $modaRoot = Category::query()->where('slug', 'moda')->first();

        if ($modaRoot === null) {
            return [];
        }

        return Category::query()
            ->where('parent_id', $modaRoot->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_DEPARTAMENTO)
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'name', 'slug', 'description'])
            ->map(fn (Category $dept) => $dept->slug === 'moda-ninos'
                ? self::formatNinosDepartment($dept)
                : self::formatStandardDepartment($dept))
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private static function formatStandardDepartment(Category $dept): array
    {
        return [
            'id' => $dept->id,
            'name' => $dept->name,
            'slug' => $dept->slug,
            'key' => str($dept->slug)->after('moda-')->toString(),
            'categories' => self::loadCategories($dept->id),
        ];
    }

    /** @return array<string, mixed> */
    private static function formatNinosDepartment(Category $dept): array
    {
        $segments = Category::query()
            ->where('parent_id', $dept->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_SEGMENTO)
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'name', 'slug']);

        return [
            'id' => $dept->id,
            'name' => $dept->name,
            'slug' => $dept->slug,
            'key' => 'ninos',
            'segments' => $segments->map(fn (Category $seg) => [
                'id' => $seg->id,
                'name' => $seg->name,
                'slug' => $seg->slug,
                'value' => str($seg->slug)->after('moda-ninos-')->toString(),
                'categories' => self::loadCategories($seg->id),
            ])->values()->all(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function loadCategories(int $parentId): array
    {
        return Category::query()
            ->where('parent_id', $parentId)
            ->where('level', FashionCategoryDefinitions::LEVEL_CATEGORIA)
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'types' => self::loadTypes($cat->id),
            ])
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private static function loadTypes(int $categoriaId): array
    {
        return Category::query()
            ->where('parent_id', $categoriaId)
            ->where('level', FashionCategoryDefinitions::LEVEL_TIPO)
            ->where('is_active', true)
            ->orderBy('position')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $tipo) => [
                'id' => $tipo->id,
                'name' => $tipo->name,
                'slug' => $tipo->slug,
            ])
            ->values()
            ->all();
    }
}
