<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Transaction;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(private RatingService $ratingService) {}

    public function store(StoreReviewRequest $request, Transaction $transaction): JsonResponse
    {
        $user = $request->user();
        $role = $user->id === $transaction->buyer_id ? 'buyer' : 'seller';
        $revieweeId = $role === 'buyer' ? $transaction->seller_id : $transaction->buyer_id;

        $review = Review::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $revieweeId,
            'role' => $role,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $this->ratingService->recalculate($revieweeId);

        return response()->json([
            'data' => new ReviewResource($review->load(['reviewer', 'reviewee'])),
        ], 201);
    }
}
