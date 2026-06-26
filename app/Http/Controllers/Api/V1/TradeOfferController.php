<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TradeOffer\StoreTradeOfferRequest;
use App\Http\Requests\TradeOffer\UpdateTradeOfferStatusRequest;
use App\Http\Resources\TradeOfferResource;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\TradeOffer;
use App\Services\TradeEligibilityService;
use App\Support\MessageBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TradeOfferController extends Controller
{
    public function __construct(private TradeEligibilityService $tradeEligibility) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $offers = TradeOffer::query()
            ->with(['targetListing.primaryImage', 'offeredListing.primaryImage', 'proposer'])
            ->where(function ($query) use ($userId): void {
                $query->where('proposer_id', $userId)
                    ->orWhereHas('targetListing', fn ($q) => $q->where('user_id', $userId));
            })
            ->latest()
            ->paginate(20);

        return TradeOfferResource::collection($offers);
    }

    public function store(StoreTradeOfferRequest $request): JsonResponse
    {
        if (! config('marketplace.trade.enabled', true)) {
            return response()->json(['message' => 'El trueque no está disponible en este momento.'], 422);
        }

        $target = Listing::findOrFail($request->integer('target_listing_id'));
        $offered = Listing::findOrFail($request->integer('offered_listing_id'));
        $user = $request->user();

        if ($target->user_id === $user->id) {
            return response()->json(['message' => 'No puedes proponer trueque sobre tu propio anuncio.'], 422);
        }

        if ($offered->user_id !== $user->id) {
            return response()->json(['message' => 'El artículo ofrecido debe ser tuyo.'], 422);
        }

        if ($proposerReason = $this->tradeEligibility->failureReason($user)) {
            return response()->json(['message' => $proposerReason], 422);
        }

        $target->loadMissing('user.profile');

        if ($ownerReason = $this->tradeEligibility->failureReason($target->user)) {
            return response()->json(['message' => 'El dueño de este anuncio aún no cumple los requisitos de trueque.'], 422);
        }

        if (! $target->accepts_trade) {
            return response()->json(['message' => 'Este anuncio no acepta trueque.'], 422);
        }

        if ($target->status !== 'active' || $offered->status !== 'active') {
            return response()->json(['message' => 'Alguno de los anuncios no está disponible.'], 422);
        }

        $duplicate = TradeOffer::query()
            ->where('proposer_id', $user->id)
            ->where('target_listing_id', $target->id)
            ->where('offered_listing_id', $offered->id)
            ->where('status', 'pending')
            ->exists();

        if ($duplicate) {
            return response()->json(['message' => 'Ya tienes una propuesta pendiente para este artículo.'], 422);
        }

        $offer = DB::transaction(function () use ($request, $target, $offered, $user) {
            $conversation = Conversation::firstOrCreate(
                ['listing_id' => $target->id, 'buyer_id' => $user->id],
                ['seller_id' => $target->user_id],
            );

            $offer = TradeOffer::create([
                'proposer_id' => $user->id,
                'target_listing_id' => $target->id,
                'offered_listing_id' => $offered->id,
                'conversation_id' => $conversation->id,
                'status' => 'pending',
                'message' => $request->input('message'),
            ]);

            $body = sprintf(
                'Propuesta de trueque: ofrezco "%s" por "%s".',
                $offered->title,
                $target->title,
            );

            if ($request->filled('message')) {
                $body .= "\n\n".$request->input('message');
            }

            $body .= "\n\nRecuerda: elige un lugar público y de día para el encuentro. Mi Ropa no asiste al intercambio presencial.";

            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => $body,
            ]);

            MessageBroadcaster::sent($message);
            $conversation->touch();

            return $offer;
        });

        return response()->json([
            'data' => new TradeOfferResource($offer->load([
                'targetListing.primaryImage',
                'offeredListing.primaryImage',
                'proposer',
            ])),
        ], 201);
    }

    public function updateStatus(UpdateTradeOfferStatusRequest $request, TradeOffer $tradeOffer): JsonResponse
    {
        Gate::authorize('view', $tradeOffer);

        $tradeOffer->loadMissing(['targetListing', 'offeredListing']);

        $newStatus = $request->status;

        $allowedTransitions = [
            'pending' => ['accepted', 'rejected', 'cancelled'],
            'accepted' => ['completed', 'cancelled'],
        ];

        $current = $tradeOffer->status;

        if (! isset($allowedTransitions[$current]) || ! in_array($newStatus, $allowedTransitions[$current], true)) {
            return response()->json([
                'message' => "No se puede cambiar el estado de '$current' a '$newStatus'.",
            ], 422);
        }

        match ($newStatus) {
            'accepted', 'rejected' => Gate::authorize('respond', $tradeOffer),
            'cancelled' => Gate::authorize('cancel', $tradeOffer),
            'completed' => Gate::authorize('complete', $tradeOffer),
        };

        DB::transaction(function () use ($tradeOffer, $newStatus, $current): void {
            $updates = ['status' => $newStatus];

            if (in_array($newStatus, ['accepted', 'rejected'], true)) {
                $updates['responded_at'] = now();
            }

            if ($newStatus === 'accepted') {
                $tradeOffer->targetListing->update(['status' => 'reserved']);
                $tradeOffer->offeredListing->update(['status' => 'reserved']);
            }

            if ($newStatus === 'cancelled' && $current === 'accepted') {
                $tradeOffer->targetListing->update(['status' => 'active']);
                $tradeOffer->offeredListing->update(['status' => 'active']);
            }

            if ($newStatus === 'completed') {
                $updates['completed_at'] = now();
                $now = now();

                $tradeOffer->targetListing->update(['status' => 'sold', 'sold_at' => $now]);
                $tradeOffer->offeredListing->update(['status' => 'sold', 'sold_at' => $now]);
            }

            $tradeOffer->update($updates);
        });

        return response()->json([
            'data' => new TradeOfferResource($tradeOffer->fresh()->load([
                'targetListing.primaryImage',
                'offeredListing.primaryImage',
                'proposer',
            ])),
        ]);
    }
}
