<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Listing\CreateListingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ListingApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Listing::with(['category', 'condition', 'location', 'primaryImage', 'user.profile'])
            ->where('status', 'active');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('condition_id')) {
            $query->where('condition_id', $request->condition_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sort = $request->get('sort', 'recent');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views_count'),
            default => $query->latest('published_at'),
        };

        $listings = $query->paginate($request->get('per_page', 16));

        return response()->json([
            'data' => ListingResource::collection($listings),
            'meta' => [
                'total' => $listings->total(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $listing = Listing::where('slug', $slug)
            ->with(['category', 'condition', 'location', 'images', 'attributes', 'user.profile'])
            ->whereIn('status', ['active', 'reserved', 'sold'])
            ->firstOrFail();

        $listing->increment('views_count');

        return response()->json([
            'data' => ListingResource::make($listing),
        ]);
    }

    public function store(StoreListingRequest $request, CreateListingAction $action, ImageUploadService $imageService): JsonResponse
    {
        $listing = $action->execute($request->user(), $request->validated());

        if ($request->hasFile('images')) {
            $imageService->storeListingImages($listing, $request->file('images'));
        }

        return response()->json([
            'data' => ListingResource::make($listing->load(['category', 'condition', 'images'])),
            'meta' => ['message' => 'Anuncio creado correctamente.'],
        ], 201);
    }

    public function update(UpdateListingRequest $request, Listing $listing, ImageUploadService $imageService): JsonResponse
    {
        $data = $request->validated();

        $listing->update(array_filter([
            'category_id' => $data['category_id'] ?? null,
            'condition_id' => $data['condition_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'is_negotiable' => $data['is_negotiable'] ?? null,
            'status' => $data['status'] ?? null,
            'published_at' => isset($data['status']) && $data['status'] === 'active' && ! $listing->published_at
                ? now() : $listing->published_at,
        ], fn ($v) => ! is_null($v)));

        if (! empty($data['remove_image_ids'])) {
            ListingImage::query()
                ->where('listing_id', $listing->id)
                ->whereIn('id', $data['remove_image_ids'])
                ->get()
                ->each(fn (ListingImage $image) => $imageService->deleteImage($image));
        }

        if ($request->hasFile('images')) {
            $imageService->storeListingImages($listing, $request->file('images'));
        }

        if (! empty($data['attributes'])) {
            $listing->attributes()->delete();
            $listing->attributes()->createMany(
                collect($data['attributes'])->map(fn ($a) => [
                    'attribute_key' => $a['key'],
                    'attribute_value' => $a['value'],
                ])->toArray()
            );
        }

        return response()->json([
            'data' => ListingResource::make($listing->load(['category', 'condition', 'attributes', 'images'])),
            'meta' => ['message' => 'Anuncio actualizado.'],
        ]);
    }

    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        Gate::authorize('delete', $listing);
        $listing->delete();

        return response()->json(['meta' => ['message' => 'Anuncio eliminado.']]);
    }

    public function addImages(Request $request, Listing $listing, ImageUploadService $imageService): JsonResponse
    {
        Gate::authorize('addImages', $listing);

        $request->validate([
            'images' => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $images = $imageService->storeListingImages($listing, $request->file('images'));

        return response()->json([
            'data' => $images->map(fn ($img) => ['id' => $img->id, 'url' => asset('storage/'.$img->path)]),
            'meta' => ['message' => 'Imágenes subidas.'],
        ]);
    }
}
