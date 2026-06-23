<?php

namespace App\Http\Controllers;

use App\Actions\Listing\CreateListingAction;
use App\Actions\Listing\UpdateListingAction;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Models\Brand;
use App\Models\Condition;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\Universe;
use App\Services\ImageUploadService;
use App\Support\CategoryCatalog;
use App\Support\CategoryVisuals;
use App\Support\FashionConditions;
use App\Support\FashionPublishCatalog;
use App\Support\FashionPublishContext;
use App\Support\ListingDisplay;
use App\Support\FashionListingRules;
use App\Support\ListingFashionPayload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function create(): Response
    {
        $catalog = CategoryCatalog::forPublishing();

        return Inertia::render('Listings/Create', [
            'fashionCategories' => $catalog['fashion'],
            'otherCategories' => $catalog['other'],
            'categories' => $catalog['fashion']->merge($catalog['other'])->values(),
            'fashionVisuals' => CategoryVisuals::fashionByDepartment(),
            'fashionPickerTree' => FashionPublishCatalog::pickerTree(),
            'fashionPublishContexts' => FashionPublishContext::leafContextMap(),
            'fashionConditions' => FashionConditions::forFilter(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'fashionColors' => config('fashion_colors'),
            'fashionListingModes' => config('fashion_publish.listing_modes'),
            'fashionUniverses' => Universe::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'accent_color', 'description']),
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
            ->with(['category', 'condition', 'location', 'images', 'attributes', 'brand', 'user.profile'])
            ->whereIn('status', ['active', 'reserved', 'sold'])
            ->firstOrFail();

        $listing->increment('views_count');

        $relatedListings = Listing::where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->where('status', 'active')
            ->with(['primaryImage', 'condition', 'location', 'category'])
            ->latest('published_at')
            ->take(4)
            ->get();

        $contact = null;
        if (auth()->check()) {
            $user = auth()->user();
            $saleMode = $listing->category->sale_mode ?? 'marketplace';
            $contact = [
                'is_owner' => $user->id === $listing->user_id,
                'can_purchase' => $saleMode === 'marketplace',
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
            'listing' => array_merge(ListingDisplay::forShowPage($listing), [
                'is_favorited' => auth()->check()
                    && auth()->user()->favorites()->where('listing_id', $listing->id)->exists(),
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

        $listing->load(['images', 'attributes', 'category.parent', 'universes', 'brand']);

        $catalog = CategoryCatalog::forPublishing();
        $attrs = ListingFashionPayload::attributesFromListing($listing);
        $isFashion = FashionListingRules::requiresPublishAttributes($listing->category);

        return Inertia::render('Listings/Edit', [
            'listing' => [
                'id' => $listing->id,
                'slug' => $listing->slug,
                'category_id' => $listing->category_id,
                'category_slug' => $listing->category->slug,
                'is_fashion' => $isFashion,
                'condition_id' => $listing->condition_id,
                'location_id' => $listing->location_id,
                'brand_id' => $listing->brand_id,
                'brand_name' => $listing->brand_id ? '' : ($listing->brand?->name ?? ''),
                'size' => $listing->size ?? '',
                'size_note' => $listing->size_note ?? '',
                'size_label' => $attrs['size_label'] ?? '',
                'size_fits_as' => $attrs['size_fits_as'] ?? '',
                'color' => $listing->color ?? '',
                'listing_mode' => $listing->listing_mode ?? 'compra_protegida',
                'listing_type' => $listing->listing_type ?? 'individual',
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'is_negotiable' => $listing->is_negotiable,
                'status' => $listing->status,
                'measurements' => [
                    'bust_cm' => $attrs['bust_cm'] ?? '',
                    'waist_cm' => $attrs['waist_cm'] ?? '',
                    'length_cm' => $attrs['length_cm'] ?? '',
                    'sole_length_cm' => $attrs['sole_length_cm'] ?? '',
                ],
                'universe_ids' => $listing->universes->pluck('id')->values(),
                'images' => $listing->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => asset('storage/'.$image->path),
                    'is_primary' => $image->is_primary,
                ])->values(),
            ],
            'fashionCategories' => $catalog['fashion'],
            'otherCategories' => $catalog['other'],
            'categories' => $catalog['fashion']->merge($catalog['other'])->values(),
            'fashionVisuals' => CategoryVisuals::fashionByDepartment(),
            'fashionPickerTree' => FashionPublishCatalog::pickerTree(),
            'fashionPublishContexts' => FashionPublishContext::leafContextMap(),
            'fashionConditions' => FashionConditions::forFilter(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'fashionColors' => config('fashion_colors'),
            'fashionListingModes' => config('fashion_publish.listing_modes'),
            'fashionUniverses' => Universe::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'accent_color', 'description']),
            'conditions' => Condition::all(['id', 'name', 'description']),
            'locations' => Location::orderBy('city')->get(['id', 'name', 'city']),
        ]);
    }

    public function update(UpdateListingRequest $request, Listing $listing, UpdateListingAction $action, ImageUploadService $imageService): RedirectResponse
    {
        $data = $request->validated();

        $listing = $action->execute($listing, $data);

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

    public function toggleFavorite(Listing $listing): RedirectResponse
    {
        $user = auth()->user();
        $toggled = $user->favorites()->toggle($listing->id);
        $isFavorited = count($toggled['attached']) > 0;

        if ($isFavorited) {
            $listing->increment('favorites_count');
        } elseif (count($toggled['detached']) > 0 && $listing->favorites_count > 0) {
            $listing->decrement('favorites_count');
        }

        return back();
    }
}
