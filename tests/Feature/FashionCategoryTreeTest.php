<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Support\FashionCategoryDefinitions;
use Database\Seeders\FashionCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FashionCategoryTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_fashion_taxonomy_seeds_three_departments_under_moda_root(): void
    {
        $this->seed(FashionCategorySeeder::class);

        $root = Category::query()->where('slug', 'moda')->first();

        $this->assertNotNull($root);
        $this->assertNull($root->level);
        $this->assertSame('moda', $root->vertical);

        $departments = Category::query()
            ->where('parent_id', $root->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_DEPARTAMENTO)
            ->orderBy('position')
            ->pluck('name')
            ->all();

        $this->assertSame(['Mujer', 'Hombre', 'Niños'], $departments);
    }

    public function test_ninos_has_bebe_nina_and_nino_segments(): void
    {
        $this->seed(FashionCategorySeeder::class);

        $ninos = Category::query()->where('slug', 'moda-ninos')->firstOrFail();

        $segments = Category::query()
            ->where('parent_id', $ninos->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_SEGMENTO)
            ->where('is_active', true)
            ->orderBy('position')
            ->pluck('name')
            ->all();

        $this->assertSame(['Bebé', 'Niña', 'Niño'], $segments);

        $this->assertNotNull(Category::query()->where('slug', 'moda-ninos-bebe-ropa-bodys')->first());
        $this->assertNotNull(Category::query()->where('slug', 'moda-ninos-nina-ropa-vestidos')->first());
        $this->assertNotNull(Category::query()->where('slug', 'moda-ninos-nino-ropa-polos')->first());
    }

    public function test_mujer_ropa_has_expected_product_types_as_leaves(): void
    {
        $this->seed(FashionCategorySeeder::class);

        $ropa = Category::query()->where('slug', 'moda-mujer-ropa')->firstOrFail();

        $tipos = Category::query()
            ->where('parent_id', $ropa->id)
            ->where('level', FashionCategoryDefinitions::LEVEL_TIPO)
            ->orderBy('position')
            ->pluck('name')
            ->all();

        $this->assertContains('Camisetas', $tipos);
        $this->assertContains('Vestidos', $tipos);
        $this->assertContains('Ropa deportiva', $tipos);
        $this->assertCount(12, $tipos);
    }

    public function test_fashion_leaf_categories_are_publishable_targets(): void
    {
        $this->seed(FashionCategorySeeder::class);

        $expectedLeaves = count(FashionCategoryDefinitions::leafSlugs());

        $actualLeaves = Category::query()
            ->moda()
            ->where('level', FashionCategoryDefinitions::LEVEL_TIPO)
            ->count()
            + Category::query()
                ->moda()
                ->where('level', FashionCategoryDefinitions::LEVEL_CATEGORIA)
                ->whereDoesntHave('children')
                ->count();

        $this->assertSame($expectedLeaves, $actualLeaves);
        $this->assertGreaterThan(80, $actualLeaves);
    }

    public function test_listings_accept_fashion_filter_columns(): void
    {
        $this->seed(FashionCategorySeeder::class);

        $category = Category::query()->where('slug', 'moda-mujer-ropa-camisetas')->firstOrFail();
        $condition = Condition::factory()->create();

        $listing = Listing::factory()->create([
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'size' => 'M',
            'color' => 'Negro',
            'listing_mode' => 'compra_protegida',
            'listing_type' => 'individual',
        ]);

        $this->assertSame('M', $listing->fresh()->size);
        $this->assertSame('individual', $listing->fresh()->listing_type);
    }
}
