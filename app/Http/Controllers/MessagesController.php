<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Support\MessageBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MessagesController extends Controller
{
    public function index(Request $request): Response
    {
        $conversations = Conversation::with(['listing.primaryImage', 'buyer', 'seller', 'lastMessage'])
            ->where('buyer_id', $request->user()->id)
            ->orWhere('seller_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        Gate::authorize('view', $conversation);

        $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return Inertia::render('Messages/Show', [
            'conversation' => $conversation->load(['listing.primaryImage', 'buyer', 'seller']),
            'messages' => $messages,
        ]);
    }

    public function storeMessage(SendMessageRequest $request, Conversation $conversation): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('view', $conversation);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        MessageBroadcaster::sent($message);

        $conversation->touch();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => (new MessageResource($message->loadMissing('sender')))->resolve(),
            ], 201);
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function fetchMessages(Request $request, Conversation $conversation): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $afterId = $request->integer('after', 0);

        $messages = $conversation->messages()
            ->with('sender')
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('created_at')
            ->get();

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => MessageResource::collection($messages),
        ]);
    }
}
