<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Universe;
use App\Support\FashionCategoryTree;
use App\Support\FashionConditions;
use App\Support\SecondLifeImpact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FashionController extends Controller
{
    public function department(Request $request, string $department): Response
    {
        $dept = FashionCategoryTree::departmentOrFail($department);
        $scopeIds = FashionCategoryTree::descendantIds($dept);

        $query = $this->baseQuery($scopeIds, $request);

        $segment = null;
        if ($department === 'ninos' && $request->filled('segment')) {
            $segmentSlug = 'moda-ninos-'.str($request->segment)->slug();
            $segment = Category::query()->where('slug', $segmentSlug)->first();
            if ($segment) {
                $scopeIds = FashionCategoryTree::descendantIds($segment);
                $query = $this->baseQuery($scopeIds, $request);
            }
        }

        if ($request->filled('categoria')) {
            $categoria = Category::query()->where('slug', $request->categoria)->first();
            if ($categoria) {
                $scopeIds = FashionCategoryTree::descendantIds($categoria);
                $query = $this->baseQuery($scopeIds, $request);
            }
        }

        if ($request->filled('tipo')) {
            $tipo = Category::query()->where('slug', $request->tipo)->first();
            if ($tipo) {
                $query->where('category_id', $tipo->id);
            }
        }

        $listings = $query->paginate(16)->withQueryString();

        $listings->getCollection()->transform(function (Listing $listing) {
            $listing->setAttribute('second_life_impact', SecondLifeImpact::forListing($listing));

            return $listing;
        });

        $selectedCategoria = $request->filled('categoria')
            ? Category::query()->where('slug', $request->categoria)->first()
            : null;

        $filterKeys = [
            'q', 'segment', 'categoria', 'tipo', 'brand', 'size', 'size_note', 'color',
            'condition', 'min_price', 'max_price', 'mode', 'universe', 'sort',
        ];

        return Inertia::render('Fashion/Department', [
            'department' => [
                'slug' => $department,
                'name' => $dept->name,
                'category_slug' => $dept->slug,
            ],
            'listings' => $listings,
            'filters' => $request->only($filterKeys),
            'segmentChips' => FashionCategoryTree::segmentChips($dept),
            'navigationChips' => FashionCategoryTree::navigationChips($dept, $segment),
            'tipos' => FashionCategoryTree::tiposForCategoria($selectedCategoria),
            'conditions' => FashionConditions::forFilter(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'sizes' => config('fashion_sizes'),
            'colors' => config('fashion_colors'),
            'universes' => Universe::query()->where('is_active', true)->orderBy('position')->get(['id', 'name', 'slug', 'accent_color']),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'href' => route('home')],
                ['label' => 'Moda', 'href' => route('home').'#moda'],
                ['label' => $dept->name, 'href' => null],
            ],
        ]);
    }

    public function universe(Request $request, string $universe): Response
    {
        $universeModel = Universe::query()->where('slug', $universe)->where('is_active', true)->firstOrFail();

        $query = Listing::query()
            ->with(['category', 'condition', 'location', 'primaryImage', 'brand'])
            ->where('status', 'active')
            ->whereHas('universes', fn (Builder $q) => $q->where('universes.id', $universeModel->id));

        $this->applyCommonFilters($query, $request);

        $listings = $query->latest('published_at')->paginate(16)->withQueryString();

        return Inertia::render('Fashion/Universe', [
            'universe' => $universeModel->only(['id', 'name', 'slug', 'accent_color', 'description']),
            'listings' => $listings,
            'filters' => $request->only(['q', 'sort', 'min_price', 'max_price']),
            'breadcrumbs' => [
                ['label' => 'Inicio', 'href' => route('home')],
                ['label' => 'Moda', 'href' => route('home').'#moda'],
                ['label' => $universeModel->name, 'href' => null],
            ],
        ]);
    }

    /** @param  list<int>  $categoryIds */
    private function baseQuery(array $categoryIds, Request $request): Builder
    {
        $query = Listing::query()
            ->with(['category', 'condition', 'location', 'primaryImage', 'brand'])
            ->where('status', 'active')
            ->whereIn('category_id', $categoryIds);

        $this->applyCommonFilters($query, $request);

        match ($request->get('sort', 'recent')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views_count'),
            default => $query->latest('published_at'),
        };

        return $query;
    }

    private function applyCommonFilters(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn (Builder $s) => $s
                ->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
            );
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('size')) {
            $query->where('size', $request->size);
        }

        if ($request->filled('size_note')) {
            $query->where('size_note', $request->size_note);
        }

        if ($request->filled('color')) {
            $query->where('color', $request->color);
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

        if ($request->filled('mode')) {
            $query->where('listing_mode', $request->mode);
        }

        if ($request->filled('universe')) {
            $query->whereHas('universes', fn (Builder $q) => $q->where('slug', $request->universe));
        }
    }
}
