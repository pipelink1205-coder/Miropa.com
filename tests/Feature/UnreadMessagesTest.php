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

class UnreadMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_messages_count_excludes_own_messages(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => 'active',
        ]);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Hola',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'body' => 'Respuesta',
        ]);

        $this->assertSame(1, $seller->unreadMessagesCount());
        $this->assertSame(1, $buyer->unreadMessagesCount());
    }

    public function test_read_messages_are_not_counted(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::factory()->create([
            'user_id' => $seller->id,
            'category_id' => Category::factory()->create()->id,
            'condition_id' => Condition::factory()->create()->id,
            'location_id' => Location::factory()->create()->id,
            'status' => 'active',
        ]);

        $conversation = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Hola',
            'read_at' => now(),
        ]);

        $this->assertSame(0, $seller->unreadMessagesCount());
    }
}
