<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class FashionCategoryTreeSync
{
    /** Sincroniza todo el árbol moda (upsert por slug). */
    public static function syncAll(): void
    {
        $position = 0;
        self::syncNode(FashionCategoryDefinitions::root(), null, [], $position);
        Cache::forget('categories.tree');
    }

    /** Reemplaza solo la rama Niños (desactiva segmentos anteriores). */
    public static function syncNinosBranch(): void
    {
        $ninos = Category::query()->where('slug', 'moda-ninos')->first();

        if ($ninos === null) {
            self::syncAll();

            return;
        }

        $legacySegmentIds = Category::query()
            ->where('parent_id', $ninos->id)
            ->pluck('id');

        if ($legacySegmentIds->isNotEmpty()) {
            Category::query()
                ->whereIn('id', $legacySegmentIds)
                ->orWhereIn('parent_id', $legacySegmentIds)
                ->update(['is_active' => false]);
        }

        $position = (int) Category::query()->where('vertical', 'moda')->max('position');
        $ninosNode = FashionCategoryDefinitions::departamentoNinos();

        foreach ($ninosNode['children'] ?? [] as $child) {
            self::syncNode($child, $ninos->id, ['Moda', 'Niños'], $position);
        }

        Cache::forget('categories.tree');
    }

    /**
     * @param  array{name: string, level: string, children?: list<array>}  $node
     * @param  list<string>  $ancestors
     */
    public static function syncNode(array $node, ?int $parentId, array $ancestors, int &$position): Category
    {
        $slug = FashionCategoryDefinitions::slug([...$ancestors, $node['name']]);

        $category = Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => $parentId,
                'name' => $node['name'],
                'level' => $node['level'] === 'vertical' ? null : $node['level'],
                'vertical' => FashionCategoryDefinitions::VERTICAL,
                'icon' => null,
                'image' => null,
                'description' => null,
                'position' => ++$position,
                'is_active' => true,
                'sale_mode' => 'marketplace',
            ],
        );

        foreach ($node['children'] ?? [] as $child) {
            self::syncNode($child, $category->id, [...$ancestors, $node['name']], $position);
        }

        return $category;
    }
}
