<?php

namespace App\Http\Controllers;

use App\Actions\Listing\CreateListingAction;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Models\Conversation;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Listings/Create', [
            'categories' => Category::whereNull('parent_id')
                ->with('children')
                ->orderBy('position')
                ->get(['id', 'name', 'icon', 'parent_id']),
            'conditions' => Condition::all(['id', 'name', 'description']),
            'locations' => Location::orderBy('city')->get(['id', 'name', 'city']),
        ]);
    }

    public function store(StoreListingRequest $request, CreateListingAction $action, ImageUploadService $imageService): RedirectResponse
    {
        $listing = $action->execute($request->user(), $request->validated());

        if ($request->hasFile('images')) {
            $imageService->storeListingImages($listing, $request->file('images'));
        }

        return redirect()->route('listings.show', $listing->slug)
            ->with('success', 'Anuncio publicado correctamente.');
    }

    public function show(string $slug): Response
    {
        $listing = Listing::where('slug', $slug)
            ->with(['category', 'condition', 'location', 'images', 'attributes', 'user.profile'])
            ->whereIn('status', ['active', 'reserved', 'sold'])
            ->firstOrFail();

        $listing->increment('views_count');

        $relatedListings = Listing::where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->where('status', 'active')
            ->with(['primaryImage', 'condition', 'location'])
            ->latest('published_at')
            ->take(4)
            ->get();

        $contact = null;
        if (auth()->check()) {
            $user = auth()->user();
            $contact = [
                'is_owner' => $user->id === $listing->user_id,
                'conversation_id' => Conversation::query()
                    ->where('listing_id', $listing->id)
                    ->where(function ($q) use ($user) {
                        $q->where('buyer_id', $user->id)
                            ->orWhere('seller_id', $user->id);
                    })
                    ->value('id'),
                'needs_email' => ! $user->hasVerifiedEmail(),
                'needs_phone' => ! $user->hasVerifiedPhone(),
            ];
        }

        return Inertia::render('Listings/Show', [
            'listing' => array_merge($listing->toArray(), [
                'seller' => [
                    'name' => $listing->user->name,
                    'username' => $listing->user->username,
                    'trust' => $listing->user->trustSummary(),
                ],
            ]),
            'relatedListings' => $relatedListings,
            'contact' => $contact,
        ]);
    }

    public function edit(Listing $listing): Response
    {
        $this->authorize('update', $listing);

        $listing->load(['images', 'attributes', 'category.parent']);

        return Inertia::render('Listings/Edit', [
            'listing' => [
                'id' => $listing->id,
                'slug' => $listing->slug,
                'category_id' => $listing->category_id,
                'condition_id' => $listing->condition_id,
                'location_id' => $listing->location_id,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'is_negotiable' => $listing->is_negotiable,
                'status' => $listing->status,
                'images' => $listing->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => asset('storage/'.$image->path),
                    'is_primary' => $image->is_primary,
                ])->values(),
            ],
            'categories' => Category::whereNull('parent_id')
                ->with('children')
                ->orderBy('position')
                ->get(['id', 'name', 'icon', 'parent_id']),
            'conditions' => Condition::all(['id', 'name']),
            'locations' => Location::orderBy('city')->get(['id', 'name', 'city']),
        ]);
    }

    public function update(UpdateListingRequest $request, Listing $listing, ImageUploadService $imageService): RedirectResponse
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
                ? now()
                : $listing->published_at,
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

        return redirect()->route('listings.show', $listing->fresh()->slug)
            ->with('success', 'Anuncio actualizado.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorize('delete', $listing);
        $listing->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Anuncio eliminado.');
    }
}
