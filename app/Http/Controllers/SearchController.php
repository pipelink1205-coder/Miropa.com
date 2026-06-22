<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = Listing::with(['category', 'condition', 'location', 'primaryImage'])
            ->where('status', 'active');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s
                ->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
            );
        }

        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $ids = $cat->children->pluck('id')->push($cat->id);
                $query->whereIn('category_id', $ids);
            }
        }

        if ($request->filled('condition')) {
            $query->where('condition_id', $request->condition);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        match ($request->get('sort', 'recent')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views_count'),
            default => $query->latest('published_at'),
        };

        $listings = $query->paginate(16)->withQueryString();

        return Inertia::render('Search/Index', [
            'listings' => $listings,
            'filters' => $request->only(['q', 'category', 'condition', 'min_price', 'max_price', 'sort']),
            'categories' => Category::whereNull('parent_id')->where('is_active', true)
                ->with('children')->orderBy('position')->get(['id', 'name', 'slug', 'icon']),
            'conditions' => Condition::all(['id', 'name']),
        ]);
    }
}
