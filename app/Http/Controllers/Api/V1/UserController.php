<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->where('status', 'active')
            ->with(['profile.location'])
            ->firstOrFail();

        $listings = $user->listings()
            ->where('status', 'active')
            ->with(['category', 'condition', 'primaryImage'])
            ->latest('published_at')
            ->paginate(12);

        $reviews = $user->reviewsReceived()
            ->with('reviewer')
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => [
                'user' => UserResource::make($user),
                'listings' => ListingResource::collection($listings),
                'reviews' => ReviewResource::collection($reviews),
            ],
        ]);
    }
}
