<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\CategoryDefinitions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $position = 1;

        foreach (CategoryDefinitions::fashion() as $data) {
            $this->createGroup($data, 'marketplace', $position++);
        }

        foreach (CategoryDefinitions::classified() as $data) {
            $this->createGroup($data, 'classified', $position++);
        }

        Cache::forget('categories.tree');
    }

    /** @param array{name: string, description: string, children: list<string>} $data */
    private function createGroup(array $data, string $saleMode, int $position): void
    {
        $parent = Category::create([
            'parent_id' => null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'icon' => null,
            'description' => $data['description'],
            'position' => $position,
            'is_active' => true,
            'sale_mode' => $saleMode,
        ]);

        foreach ($data['children'] as $childPos => $childName) {
            Category::create([
                'parent_id' => $parent->id,
                'name' => $childName,
                'slug' => Str::slug($data['name'].' '.$childName),
                'icon' => null,
                'description' => null,
                'position' => $childPos + 1,
                'is_active' => true,
                'sale_mode' => $saleMode,
            ]);
        }
    }
}
