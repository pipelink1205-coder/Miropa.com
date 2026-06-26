<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\TradeOfferController as ApiTradeOfferController;
use App\Http\Requests\TradeOffer\StoreTradeOfferRequest;
use App\Http\Requests\TradeOffer\UpdateTradeOfferStatusRequest;
use App\Models\TradeOffer;
use App\Services\TradeEligibilityService;
use App\Support\TradeOfferPresenter;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TradeOfferController extends Controller
{
    public function __construct(
        private ApiTradeOfferController $api,
        private TradeEligibilityService $eligibility,
    ) {}

    public function index(): Response
    {
        $userId = auth()->id();

        $offers = TradeOffer::query()
            ->with([
                'proposer',
                'targetListing.primaryImage',
                'targetListing.user',
                'offeredListing.primaryImage',
            ])
            ->where(function ($query) use ($userId): void {
                $query->where('proposer_id', $userId)
                    ->orWhereHas('targetListing', fn ($q) => $q->where('user_id', $userId));
            })
            ->latest()
            ->get()
            ->map(fn (TradeOffer $offer) => array_merge(
                TradeOfferPresenter::forInertia($offer),
                [
                    'is_proposer' => $offer->proposer_id === $userId,
                    'is_target_owner' => $offer->targetListing->user_id === $userId,
                ],
            ))
            ->values();

        $user = auth()->user();

        return Inertia::render('TradeOffers/Index', [
            'offers' => $offers,
            'tradeEligibility' => [
                'eligible' => $this->eligibility->isEligible($user),
                'reason' => $this->eligibility->failureReason($user),
            ],
        ]);
    }

    public function store(StoreTradeOfferRequest $request): JsonResponse
    {
        return $this->api->store($request);
    }

    public function updateStatus(UpdateTradeOfferStatusRequest $request, TradeOffer $tradeOffer): JsonResponse
    {
        return $this->api->updateStatus($request, $tradeOffer);
    }
}
