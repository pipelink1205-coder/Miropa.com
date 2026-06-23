<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Support\MessageBroadcaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListingContactController extends Controller
{
    public function __invoke(Request $request, Listing $listing): RedirectResponse
    {
        if ($listing->user_id === $request->user()->id) {
            return back()->with('error', 'No puedes contactarte contigo mismo.');
        }

        if (! in_array($listing->status, ['active', 'reserved'], true)) {
            return back()->with('error', 'Este anuncio no está disponible para contactar.');
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::firstOrCreate(
            ['listing_id' => $listing->id, 'buyer_id' => $request->user()->id],
            ['seller_id' => $listing->user_id]
        );

        if (! empty($validated['body'])) {
            $message = $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'body' => $validated['body'],
            ]);

            MessageBroadcaster::sent($message);
            $conversation->touch();
        }

        return redirect()->route('messages.show', $conversation);
    }
}
