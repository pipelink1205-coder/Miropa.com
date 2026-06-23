<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\FashionConditions;
use App\Support\ListingDisplay;
use Database\Seeders\FashionCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FashionCategorySeeder::class);
    }

    public function test_show_page_displays_formatted_detail_rows_not_raw_json(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->where('slug', 'moda-mujer-calzado-tacones')->firstOrFail();
        $condition = FashionConditions::forFilter()->first();
        $brand = Brand::factory()->create(['name' => 'Steve Madden', 'slug' => 'steve-madden']);

        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'brand_id' => $brand->id,
            'title' => 'Tacones negros elegantes',
            'slug' => 'tacones-negros-elegantes',
            'description' => 'Tacones como nuevos, solo tienen una postura.',
            'size' => '38',
            'color' => 'Negro',
            'listing_mode' => 'compra_protegida',
            'status' => 'active',
            'published_at' => now(),
        ]);

        $listing->attributes()->create([
            'attribute_key' => 'sole_length_cm',
            'attribute_value' => '16',
        ]);

        $this->get('/anuncios/tacones-negros-elegantes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Listings/Show')
                ->has('listing.detail_rows')
                ->where('listing.detail_rows', fn ($rows) => collect($rows)->contains(
                    fn ($row) => $row['label'] === 'Longitud plantilla' && $row['value'] === '16 cm',
                ))
                ->where('listing.size', '38')
                ->where('listing.color', 'Negro')
            );
    }

    public function test_listing_display_builds_readable_rows(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->where('slug', 'moda-mujer-ropa-vestidos')->firstOrFail();
        $condition = FashionConditions::forFilter()->first();

        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'size' => 'M',
            'color' => 'Rosa',
            'status' => 'active',
        ]);

        $listing->load(['brand', 'attributes']);

        $rows = ListingDisplay::detailRows($listing);

        $this->assertTrue(collect($rows)->contains(fn ($r) => $r['label'] === 'Talla' && $r['value'] === 'M'));
        $this->assertTrue(collect($rows)->contains(fn ($r) => $r['label'] === 'Color' && $r['value'] === 'Rosa'));
    }
}
