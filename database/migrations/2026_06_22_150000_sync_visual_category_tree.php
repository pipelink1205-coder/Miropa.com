<?php

use App\Models\Category;
use App\Support\CategoryDefinitions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (CategoryDefinitions::parentSlugRenames() as $oldSlug => $newSlug) {
            Category::query()->where('slug', $oldSlug)->update([
                'slug' => $newSlug,
                'name' => match ($newSlug) {
                    'ninos' => 'Niños',
                    'deportiva' => 'Deportiva',
                    'hogar' => 'Hogar',
                    'arte' => 'Arte',
                    'libros' => 'Libros',
                    default => $newSlug,
                },
            ]);
        }

        $position = 1;

        foreach (CategoryDefinitions::fashion() as $data) {
            $this->upsertGroup($data, 'marketplace', $position++);
        }

        foreach (CategoryDefinitions::classified() as $data) {
            $this->upsertGroup($data, 'classified', $position++);
        }

        $slugToId = Category::query()->pluck('id', 'slug');

        foreach (CategoryDefinitions::listingSlugMap() as $oldSlug => $newSlug) {
            if (! $slugToId->has($oldSlug) || ! $slugToId->has($newSlug)) {
                continue;
            }

            DB::table('listings')
                ->where('category_id', $slugToId[$oldSlug])
                ->update(['category_id' => $slugToId[$newSlug]]);
        }

        $legacyParentIds = Category::query()
            ->whereIn('slug', CategoryDefinitions::legacySlugsToDeactivate())
            ->pluck('id');

        if ($legacyParentIds->isNotEmpty()) {
            Category::query()
                ->whereIn('id', $legacyParentIds)
                ->orWhereIn('parent_id', $legacyParentIds)
                ->update(['is_active' => false]);
        }

        Cache::forget('categories.tree');
    }

    /** @param array{name: string, description: string, children: list<string>} $data */
    private function upsertGroup(array $data, string $saleMode, int $position): void
    {
        $parentSlug = Str::slug($data['name']);

        $parent = Category::query()->updateOrCreate(
            ['slug' => $parentSlug],
            [
                'parent_id' => null,
                'name' => $data['name'],
                'icon' => null,
                'description' => $data['description'],
                'position' => $position,
                'is_active' => true,
                'sale_mode' => $saleMode,
            ],
        );

        $childSlugs = [];

        foreach ($data['children'] as $childPos => $childName) {
            $childSlug = Str::slug($data['name'].' '.$childName);
            $childSlugs[] = $childSlug;

            Category::query()->updateOrCreate(
                ['slug' => $childSlug],
                [
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'icon' => null,
                    'description' => null,
                    'position' => $childPos + 1,
                    'is_active' => true,
                    'sale_mode' => $saleMode,
                ],
            );
        }

        Category::query()
            ->where('parent_id', $parent->id)
            ->whereNotIn('slug', $childSlugs)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        Cache::forget('categories.tree');
    }
};
