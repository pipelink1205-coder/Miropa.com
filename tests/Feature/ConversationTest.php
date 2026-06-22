<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    private function makeListing(User $seller): Listing
    {
        $category = Category::factory()->create();
        $condition = Condition::factory()->create();
        $location = Location::factory()->create();

        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'location_id' => $location->id,
            'status' => 'active',
        ]);
    }

    public function test_buyer_can_start_conversation(): void
    {
        Event::fake();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $response = $this->actingAs($buyer)
            ->postJson('/api/v1/conversations', [
                'listing_id' => $listing->id,
                'body' => '¿Está disponible?',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.listing.id', $listing->id);

        $this->assertDatabaseHas('conversations', [
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $buyer->id,
            'body' => '¿Está disponible?',
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_seller_cannot_message_own_listing(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($seller)
            ->postJson('/api/v1/conversations', [
                'listing_id' => $listing->id,
                'body' => 'Hola',
            ])
            ->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_start_conversation(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->postJson('/api/v1/conversations', [
            'listing_id' => $listing->id,
            'body' => 'Hola',
        ])->assertStatus(401);
    }

    public function test_participant_can_send_message(): void
    {
        Event::fake();

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->postJson("/api/v1/conversations/{$conversation->id}/messages", ['body' => 'Sí, disponible'])
            ->assertStatus(201)
            ->assertJsonPath('data.body', 'Sí, disponible');

        Event::assertDispatched(MessageSent::class);
    }

    public function test_non_participant_cannot_send_message(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $outsider = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($outsider)
            ->postJson("/api/v1/conversations/{$conversation->id}/messages", ['body' => 'Hola'])
            ->assertStatus(403);
    }

    public function test_participant_can_send_message_via_web_json(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($buyer)
            ->postJson("/mensajes/{$conversation->id}/messages", ['body' => '¿Sigue disponible?'])
            ->assertCreated()
            ->assertJsonPath('data.body', '¿Sigue disponible?')
            ->assertJsonPath('data.sender_id', $buyer->id);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => '¿Sigue disponible?',
        ]);
    }

    public function test_participant_can_send_message_via_web(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post("/mensajes/{$conversation->id}/messages", ['body' => 'Sí, disponible'])
            ->assertRedirect(route('messages.show', $conversation));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'body' => 'Sí, disponible',
        ]);
    }

    public function test_participant_can_list_messages(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $conversation->messages()->create(['sender_id' => $buyer->id, 'body' => 'Hola']);
        $conversation->messages()->create(['sender_id' => $seller->id, 'body' => 'Hola, sí disponible']);

        $this->actingAs($buyer)
            ->getJson("/api/v1/conversations/{$conversation->id}/messages")
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_buyer_can_contact_seller_via_web(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $response = $this->actingAs($buyer)
            ->post("/anuncios/{$listing->slug}/contactar", [
                'body' => '¿Sigue disponible?',
            ]);

        $conversation = Conversation::first();

        $response->assertRedirect(route('messages.show', $conversation));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => '¿Sigue disponible?',
        ]);
    }

    public function test_seller_cannot_contact_own_listing_via_web(): void
    {
        $seller = User::factory()->create();
        $listing = $this->makeListing($seller);

        $this->actingAs($seller)
            ->post("/anuncios/{$listing->slug}/contactar", ['body' => 'Hola'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_participant_can_fetch_new_messages_via_web(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = $this->makeListing($seller);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $first = $conversation->messages()->create(['sender_id' => $buyer->id, 'body' => 'Hola']);
        $second = $conversation->messages()->create(['sender_id' => $seller->id, 'body' => 'Respuesta']);

        $this->actingAs($buyer)
            ->getJson("/mensajes/{$conversation->id}/messages?after={$first->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'Respuesta');
    }
}
