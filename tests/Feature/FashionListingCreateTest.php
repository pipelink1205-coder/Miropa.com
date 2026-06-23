<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Support\FashionConditions;
use Database\Seeders\FashionCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FashionListingCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FashionCategorySeeder::class);
    }

    public function test_create_page_includes_fashion_publish_wizard_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/listings/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Listings/Create')
                ->has('fashionPickerTree', 3)
                ->has('fashionPublishContexts')
                ->has('fashionConditions')
                ->has('fashionColors')
                ->has('brands')
            );
    }

    public function test_user_can_create_fashion_listing_with_size_and_color(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->where('slug', 'moda-mujer-ropa-camisetas')->firstOrFail();
        $condition = FashionConditions::forFilter()->first();
        $brand = Brand::factory()->create(['name' => 'Zara', 'slug' => 'zara']);

        $this->actingAs($user)
            ->post('/listings', [
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'brand_id' => $brand->id,
                'size' => 'M',
                'color' => 'Negro',
                'listing_mode' => 'compra_protegida',
                'listing_type' => 'individual',
                'title' => 'Camiseta básica negra Zara',
                'description' => 'Camiseta en algodón, poco uso, sin manchas ni roturas visibles.',
                'price' => 45000,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('listings', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'size' => 'M',
            'color' => 'Negro',
            'brand_id' => $brand->id,
            'listing_mode' => 'compra_protegida',
        ]);
    }

    public function test_fashion_listing_requires_size_and_color_for_ropa(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->where('slug', 'moda-mujer-ropa-camisetas')->firstOrFail();
        $condition = FashionConditions::forFilter()->first();

        $this->actingAs($user)
            ->post('/listings', [
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'title' => 'Camiseta sin detalles moda',
                'description' => 'Descripción válida pero faltan talla y color obligatorios.',
                'price' => 30000,
                'status' => 'draft',
            ])
            ->assertSessionHasErrors(['size', 'color']);
    }

    public function test_fashion_category_must_be_leaf_tipo(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->where('slug', 'moda-mujer-ropa')->firstOrFail();
        $condition = FashionConditions::forFilter()->first();

        $this->actingAs($user)
            ->post('/listings', [
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'size' => 'M',
                'color' => 'Negro',
                'title' => 'Publicación en categoría intermedia',
                'description' => 'Intento publicar en nivel categoría en vez de tipo hoja.',
                'price' => 50000,
                'status' => 'draft',
            ])
            ->assertSessionHasErrors(['category_id']);
    }
}
