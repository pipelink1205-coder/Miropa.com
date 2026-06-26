<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSaleModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'marketplace.checkout_enabled' => true,
            'marketplace.commission.marketplace' => 0.05,
        ]);
    }

    private function makeListing(User $seller, Category $category, int $price = 100_000): Listing
    {
        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => 'active',
            'price' => $price,
        ]);
    }

    public function test_classified_listing_cannot_create_transaction_via_api(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->classified()->create();
        $listing = $this->makeListing($seller, $category);

        $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Este anuncio es de contacto directo y no admite compra en la plataforma.',
            );
    }

    public function test_marketplace_listing_can_create_transaction_via_api(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create(['sale_mode' => 'marketplace']);
        $listing = $this->makeListing($seller, $category);

        $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_commission_amount_uses_config_rate(): void
    {
        config(['marketplace.commission.marketplace' => 0.08]);

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create(['sale_mode' => 'marketplace']);
        $listing = $this->makeListing($seller, $category, 100_000);

        $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.commission_amount', '8000.00');

        $this->assertDatabaseHas('transactions', [
            'listing_id' => $listing->id,
            'commission_amount' => 8000,
        ]);
    }

    public function test_transaction_blocked_when_checkout_disabled(): void
    {
        config(['marketplace.checkout_enabled' => false]);

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create(['sale_mode' => 'marketplace']);
        $listing = $this->makeListing($seller, $category);

        $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Las compras en la plataforma no están disponibles aún.');
    }
}
