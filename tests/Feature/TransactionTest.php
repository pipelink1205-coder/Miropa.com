<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function makeListing(User $seller, string $status = 'active'): Listing
    {
        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => $status,
            'price' => 100000,
        ]);
    }

    public function test_buyer_can_create_transaction(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $response = $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.amount', '100000.00');

        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'status' => 'reserved']);
    }

    public function test_seller_cannot_buy_own_listing(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($seller)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422);
    }

    public function test_cannot_buy_inactive_listing(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller, 'sold');

        $this->actingAs($buyer)
            ->postJson('/api/v1/transactions', [
                'listing_id' => $listing->id,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422);
    }

    public function test_can_update_transaction_status(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $transaction = Transaction::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => 100000,
            'commission_amount' => 5000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'shipping_method' => 'in_person',
        ]);

        $this->actingAs($buyer)
            ->patchJson("/api/v1/transactions/{$transaction->id}/status", ['status' => 'paid'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'paid');
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $transaction = Transaction::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => 100000,
            'commission_amount' => 5000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'shipping_method' => 'in_person',
        ]);

        $this->actingAs($buyer)
            ->patchJson("/api/v1/transactions/{$transaction->id}/status", ['status' => 'delivered'])
            ->assertStatus(422);
    }

    public function test_buyer_can_leave_review_on_completed_transaction(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        Profile::factory()->create(['user_id' => $seller->id]);
        Profile::factory()->create(['user_id' => $buyer->id]);

        $transaction = Transaction::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => 100000,
            'commission_amount' => 5000,
            'status' => 'completed',
            'payment_method' => 'cash',
            'shipping_method' => 'in_person',
            'completed_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->postJson("/api/v1/transactions/{$transaction->id}/reviews", [
                'rating' => 5,
                'comment' => 'Excelente vendedor',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $seller->id,
            'ratings_count' => 1,
        ]);
    }

    public function test_cannot_review_pending_transaction(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $transaction = Transaction::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'amount' => 100000,
            'commission_amount' => 5000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'shipping_method' => 'in_person',
        ]);

        $this->actingAs($buyer)
            ->postJson("/api/v1/transactions/{$transaction->id}/reviews", [
                'rating' => 5,
            ])
            ->assertStatus(403);
    }
}
