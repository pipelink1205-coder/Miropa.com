<?php

use App\Models\Category;
use App\Support\CategoryDefinitions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $fashionSlugs = CategoryDefinitions::fashionSlugs();

        $parentIds = Category::query()
            ->whereIn('slug', $fashionSlugs)
            ->pluck('id');

        if ($parentIds->isNotEmpty()) {
            Category::query()
                ->whereIn('id', $parentIds)
                ->orWhereIn('parent_id', $parentIds)
                ->update(['vertical' => 'moda']);
        }

        Category::query()
            ->whereNull('vertical')
            ->update(['vertical' => 'general']);
    }

    public function down(): void
    {
        Category::query()->update(['vertical' => null]);
    }
};
