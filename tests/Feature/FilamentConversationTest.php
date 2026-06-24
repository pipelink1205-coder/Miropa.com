<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentConversationTest extends TestCase
{
    use RefreshDatabase;

    private function makeListing(User $seller): Listing
    {
        return Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_access_conversations_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);

        $this->actingAs($admin)
            ->get('/admin/conversations')
            ->assertOk();
    }

    public function test_non_admin_cannot_access_conversations_index(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/conversations')
            ->assertForbidden();
    }

    public function test_admin_can_view_conversation_thread(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'status' => 'active']);
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = $this->makeListing($seller);

        $conversation = Conversation::query()->create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => '¿Sigue disponible?',
        ]);

        $this->actingAs($admin)
            ->get("/admin/conversations/{$conversation->id}")
            ->assertOk();
    }
}
