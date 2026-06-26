<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Profile;
use App\Models\TradeOffer;
use App\Models\User;
use Database\Seeders\ConditionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeOfferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConditionSeeder::class);
        config(['marketplace.trade.enabled' => true]);
    }

    private function tradeEligibleUser(array $userAttrs = [], array $profileAttrs = []): User
    {
        $user = User::factory()->verified()->create($userAttrs);

        Profile::factory()->create(array_merge([
            'user_id' => $user->id,
            'member_since' => now()->subDays(60),
            'sales_count' => 2,
            'purchases_count' => 0,
            'ratings_count' => 0,
            'rating_avg' => 0,
        ], $profileAttrs));

        return $user->fresh('profile');
    }

    private function activeListing(User $owner, bool $acceptsTrade = false): Listing
    {
        $factory = Listing::factory();

        if ($acceptsTrade) {
            $factory = $factory->acceptsTrade();
        }

        return $factory->create([
            'user_id' => $owner->id,
            'category_id' => Category::factory()->create(['sale_mode' => 'marketplace'])->id,
            'status' => 'active',
            'accepts_trade' => $acceptsTrade,
        ]);
    }

    private function offerPayload(Listing $target, Listing $offered, ?string $message = null): array
    {
        return array_filter([
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'message' => $message,
        ], fn ($value) => $value !== null);
    }

    public function test_can_create_trade_offer_against_eligible_target(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered, 'Podemos vernos el sábado.'))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.conversation_id', fn ($id) => $id !== null);

        $this->assertDatabaseHas('trade_offers', [
            'proposer_id' => $proposer->id,
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'status' => 'pending',
        ]);

        $conversation = Conversation::query()->where('listing_id', $target->id)->first();
        $this->assertNotNull($conversation);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $proposer->id,
        ]);
        $this->assertStringContainsString('Propuesta de trueque', Message::query()->latest('id')->first()->body);
    }

    public function test_cannot_create_trade_offer_when_target_does_not_accept_trade(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: false);
        $offered = $this->activeListing($proposer);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Este anuncio no acepta trueque.');
    }

    public function test_cannot_propose_trade_on_own_listing(): void
    {
        $user = $this->tradeEligibleUser();
        $target = $this->activeListing($user, acceptsTrade: true);
        $offered = $this->activeListing($user);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'No puedes proponer trueque sobre tu propio anuncio.');
    }

    public function test_offered_listing_must_belong_to_proposer(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $otherUser = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($otherUser);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'El artículo ofrecido debe ser tuyo.');
    }

    public function test_duplicate_pending_offer_is_rejected(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(201);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ya tienes una propuesta pendiente para este artículo.');
    }

    public function test_only_target_owner_can_accept_or_reject(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $intruder = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $offerId = TradeOffer::factory()->create([
            'proposer_id' => $proposer->id,
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'status' => 'pending',
        ])->id;

        $this->actingAs($intruder, 'sanctum')
            ->patchJson("/api/v1/trade-offers/{$offerId}/status", ['status' => 'accepted'])
            ->assertForbidden();

        $this->actingAs($targetOwner, 'sanctum')
            ->patchJson("/api/v1/trade-offers/{$offerId}/status", ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->assertSame('reserved', $target->fresh()->status);
        $this->assertSame('reserved', $offered->fresh()->status);
    }

    public function test_completed_marks_both_listings_as_sold(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $offer = TradeOffer::factory()->create([
            'proposer_id' => $proposer->id,
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $this->actingAs($proposer, 'sanctum')
            ->patchJson("/api/v1/trade-offers/{$offer->id}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertSame('sold', $target->fresh()->status);
        $this->assertSame('sold', $offered->fresh()->status);
        $this->assertNotNull($target->fresh()->sold_at);
        $this->assertNotNull($offered->fresh()->sold_at);
    }

    public function test_cancelled_from_accepted_reverts_listings_to_active(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $offer = TradeOffer::factory()->create([
            'proposer_id' => $proposer->id,
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $target->update(['status' => 'reserved']);
        $offered->update(['status' => 'reserved']);

        $this->actingAs($proposer, 'sanctum')
            ->patchJson("/api/v1/trade-offers/{$offer->id}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame('active', $target->fresh()->status);
        $this->assertSame('active', $offered->fresh()->status);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $offer = TradeOffer::factory()->create([
            'proposer_id' => $proposer->id,
            'target_listing_id' => $target->id,
            'offered_listing_id' => $offered->id,
            'status' => 'pending',
        ]);

        $this->actingAs($targetOwner, 'sanctum')
            ->patchJson("/api/v1/trade-offers/{$offer->id}/status", ['status' => 'completed'])
            ->assertStatus(422)
            ->assertJsonPath('message', "No se puede cambiar el estado de 'pending' a 'completed'.");
    }

    public function test_ineligible_proposer_cannot_create_trade_offer(): void
    {
        $targetOwner = $this->tradeEligibleUser();
        $proposer = User::factory()->phoneVerified()->create();
        Profile::factory()->create([
            'user_id' => $proposer->id,
            'member_since' => now()->subDays(2),
            'sales_count' => 0,
            'purchases_count' => 0,
        ]);

        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Verifica tu identidad con documento para usar trueque.');
    }

    public function test_ineligible_target_owner_blocks_trade_offer(): void
    {
        $targetOwner = User::factory()->phoneVerified()->create();
        Profile::factory()->create([
            'user_id' => $targetOwner->id,
            'member_since' => now()->subDays(60),
            'sales_count' => 0,
            'purchases_count' => 0,
        ]);

        $proposer = $this->tradeEligibleUser();
        $target = $this->activeListing($targetOwner, acceptsTrade: true);
        $offered = $this->activeListing($proposer);

        $this->actingAs($proposer, 'sanctum')
            ->postJson('/api/v1/trade-offers', $this->offerPayload($target, $offered))
            ->assertStatus(422)
            ->assertJsonPath('message', 'El dueño de este anuncio aún no cumple los requisitos de trueque.');
    }
}
