<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\FashionCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FashionDepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FashionCategorySeeder::class);
    }

    public function test_fashion_department_page_loads_for_mujer(): void
    {
        $response = $this->get('/moda/mujer');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Fashion/Department')
            ->where('department.slug', 'mujer')
            ->has('navigationChips')
            ->has('filters')
        );
    }

    public function test_ninos_department_supports_bebe_segment_filter(): void
    {
        $response = $this->get('/moda/ninos?segment=bebe');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('filters.segment', 'bebe')
        );
    }

    public function test_ninos_department_shows_category_chips_after_segment_selected(): void
    {
        $response = $this->get('/moda/ninos?segment=nina');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('filters.segment', 'nina')
            ->has('segmentChips', 3)
            ->has('navigationChips')
            ->where('navigationChips.0.name', 'Ropa')
        );
    }

    public function test_fashion_filters_persist_in_querystring(): void
    {
        $condition = Condition::query()->where('slug', 'como-nuevo')->first()
            ?? Condition::factory()->create(['slug' => 'como-nuevo-test', 'name' => 'Como nuevo']);
        $category = Category::query()->where('slug', 'moda-mujer-ropa-camisetas')->firstOrFail();
        $user = User::factory()->create();

        Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'size' => 'M',
            'color' => 'Negro',
            'status' => 'active',
        ]);

        $response = $this->get('/moda/mujer?size=M&color=Negro&categoria=moda-mujer-ropa');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('filters.size', 'M')
            ->where('filters.color', 'Negro')
            ->where('listings.total', 1)
        );
    }
}
