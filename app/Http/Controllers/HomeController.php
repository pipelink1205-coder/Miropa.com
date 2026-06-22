<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $categories = collect(config('category_images'))
            ->map(fn (array $category) => [
                'name' => $category['name'],
                'image' => asset('images/categories/'.$category['image']),
                'url' => $category['search_category']
                    ? route('search', ['category' => $category['search_category']])
                    : route('search'),
            ])
            ->values();

        $listingQuery = fn () => Listing::with(['location', 'primaryImage'])
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

        $activeListingsCount = Listing::where('status', 'active')->count();

        return Inertia::render('Home', [
            'categories' => $categories,
            'recentListings' => $recentListings,
            'featuredListings' => $featuredListings,
            'featuredSellers' => $featuredSellers,
            'stats' => [
                'active_listings' => $activeListingsCount,
                'categories' => $categories->count(),
            ],
        ]);
    }
}
