<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingSaleModeTest extends TestCase
{
    use RefreshDatabase;

    private function createListingForCategory(Category $category): Listing
    {
        $seller = User::factory()->create();
        $condition = Condition::factory()->create();
        $location = Location::factory()->create();

        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'location_id' => $location->id,
            'status' => 'active',
            'published_at' => now(),
        ]);
    }

    public function test_classified_listing_does_not_expose_purchase_action(): void
    {
        $category = Category::query()->where('slug', 'vehiculos-carros')->firstOrFail();
        $listing = $this->createListingForCategory($category);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->get("/anuncios/{$listing->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Listings/Show')
                ->where('listing.category.sale_mode', 'classified')
                ->where('contact.can_purchase', false)
            );
    }

    public function test_marketplace_listing_exposes_purchase_action(): void
    {
        $category = Category::query()->where('slug', 'calzado-tenis-y-zapatillas')->firstOrFail();
        $listing = $this->createListingForCategory($category);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->get("/anuncios/{$listing->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Listings/Show')
                ->where('listing.category.sale_mode', 'marketplace')
                ->where('contact.can_purchase', true)
            );
    }
}
