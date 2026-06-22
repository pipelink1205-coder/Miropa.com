<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        $user = $request->user();
        $favorited = $user->favorites()->toggle($listing->id);

        $isFavorited = count($favorited['attached']) > 0;

        // Mantener el contador en sync
        $listing->increment($isFavorited ? 'favorites_count' : 'favorites_count', $isFavorited ? 1 : -1);
        if (! $isFavorited) {
            $listing->decrement('favorites_count');
        }

        return response()->json([
            'data' => ['is_favorited' => $isFavorited],
            'meta' => ['message' => $isFavorited ? 'Agregado a favoritos.' : 'Eliminado de favoritos.'],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $listings = $request->user()
            ->favorites()
            ->with(['primaryImage', 'category', 'condition', 'location'])
            ->where('status', 'active')
            ->paginate(16);

        return response()->json([
            'data' => ListingResource::collection($listings),
            'meta' => ['total' => $listings->total()],
        ]);
    }
}
