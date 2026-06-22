<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Listing;
use App\Support\MessageBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = Conversation::with(['listing.primaryImage', 'buyer', 'seller', 'lastMessage'])
            ->where(function ($q) use ($request) {
                $q->where('buyer_id', $request->user()->id)
                    ->orWhere('seller_id', $request->user()->id);
            })
            ->orderByDesc('updated_at')
            ->paginate(20);

        return ConversationResource::collection($conversations);
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $listing = Listing::findOrFail($request->listing_id);

        if ($listing->user_id === $request->user()->id) {
            return response()->json(['message' => 'No puedes iniciar una conversación con tu propio anuncio.'], 422);
        }

        $conversation = Conversation::firstOrCreate(
            ['listing_id' => $listing->id, 'buyer_id' => $request->user()->id],
            ['seller_id' => $listing->user_id]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        MessageBroadcaster::sent($message);

        $conversation->touch();

        return response()->json([
            'data' => new ConversationResource($conversation->load(['listing.primaryImage', 'buyer', 'seller', 'lastMessage'])),
        ], 201);
    }

    public function messages(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        Gate::authorize('view', $conversation);

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderByDesc('created_at')
            ->paginate(50);

        return MessageResource::collection($messages);
    }

    public function sendMessage(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        MessageBroadcaster::sent($message->load('sender'));

        $conversation->touch();

        return response()->json(['data' => new MessageResource($message->load('sender'))], 201);
    }
}
