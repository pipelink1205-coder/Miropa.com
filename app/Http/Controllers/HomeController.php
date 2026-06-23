<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Universe;
use App\Models\User;
use App\Support\CategoryVisuals;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $listingQuery = fn () => Listing::with(['location', 'primaryImage', 'category'])
            ->where('status', 'active');

        $recentListings = $listingQuery()
            ->latest('published_at')
            ->limit(8)
            ->get();

        $featuredListings = $listingQuery()
            ->orderByDesc('views_count')
            ->limit(4)
            ->get();

        $featuredSellers = User::query()
            ->where('status', 'active')
            ->whereHas('listings', fn ($query) => $query->where('status', 'active'))
            ->withCount(['listings as active_listings_count' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('active_listings_count')
            ->limit(6)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_path' => $user->avatar_path,
                'active_listings_count' => $user->active_listings_count,
                'trust' => $user->trustSummary(),
                'profile_url' => route('profile.show', $user->username),
            ])
            ->values();

        $fashionCategories = CategoryVisuals::fashion();
        $otherCategories = CategoryVisuals::marketplace();

        return Inertia::render('Home', [
            'fashionCategories' => $fashionCategories,
            'otherCategories' => $otherCategories,
            'fashionUniverses' => Universe::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'accent_color', 'description']),
            'recentListings' => $recentListings,
            'featuredListings' => $featuredListings,
            'featuredSellers' => $featuredSellers,
            'stats' => [
                'active_listings' => Listing::where('status', 'active')->count(),
                'categories' => count($fashionCategories),
            ],
        ]);
    }
}
