<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FashionListingCategoryMigrator
{
    /** @var array<string, int> */
    private array $categoryIdsBySlug = [];

    /** @var array{mapped: int, unclassified: int, skipped: int, details: list<array<string, mixed>>} */
    private array $report = [
        'mapped' => 0,
        'unclassified' => 0,
        'skipped' => 0,
        'details' => [],
    ];

    public function __construct()
    {
        $this->categoryIdsBySlug = Category::query()->pluck('id', 'slug')->all();
    }

    /** @return array{mapped: int, unclassified: int, skipped: int, details: list<array<string, mixed>>} */
    public function migrate(bool $dryRun = false): array
    {
        $this->ensureSinClasificarCategory($dryRun);

        $legacyIds = Category::query()
            ->where(function ($query) {
                $query->whereIn('slug', FashionLegacyCategoryMap::legacyFashionParentSlugs())
                    ->orWhereIn('parent_id', Category::query()
                        ->whereIn('slug', FashionLegacyCategoryMap::legacyFashionParentSlugs())
                        ->pluck('id'));
            })
            ->pluck('id');

        Listing::query()
            ->whereIn('category_id', $legacyIds)
            ->with('category.parent')
            ->orderBy('id')
            ->each(fn (Listing $listing) => $this->migrateListing($listing, $dryRun));

        return $this->report;
    }

    /** @return array{restored: int} */
    public function rollback(): array
    {
        $restored = 0;

        DB::table('listing_category_migrations')
            ->orderByDesc('id')
            ->lazy()
            ->each(function ($row) use (&$restored) {
                Listing::query()
                    ->where('id', $row->listing_id)
                    ->update(['category_id' => $row->old_category_id]);

                DB::table('listing_category_migrations')->where('id', $row->id)->delete();
                $restored++;
            });

        return ['restored' => $restored];
    }

    private function migrateListing(Listing $listing, bool $dryRun): void
    {
        if (DB::table('listing_category_migrations')->where('listing_id', $listing->id)->exists()) {
            $this->report['skipped']++;

            return;
        }

        $oldCategory = $listing->category;
        if ($oldCategory === null) {
            $this->report['skipped']++;

            return;
        }

        [$newSlug, $reason, $status] = $this->resolveNewSlug($listing, $oldCategory);
        $newCategoryId = $this->categoryIdsBySlug[$newSlug] ?? null;

        if ($newCategoryId === null) {
            $newSlug = FashionLegacyCategoryMap::sinClasificarSlug();
            $newCategoryId = $this->categoryIdsBySlug[$newSlug] ?? null;
            $status = 'unclassified';
            $reason = 'Categoría destino no encontrada: '.$reason;
        }

        if ($status === 'unclassified') {
            $this->report['unclassified']++;
        } else {
            $this->report['mapped']++;
        }

        $this->report['details'][] = [
            'listing_id' => $listing->id,
            'title' => $listing->title,
            'old_slug' => $oldCategory->slug,
            'new_slug' => $newSlug,
            'status' => $status,
            'reason' => $reason,
        ];

        if ($dryRun) {
            return;
        }

        DB::transaction(function () use ($listing, $oldCategory, $newCategoryId, $status, $reason) {
            DB::table('listing_category_migrations')->insert([
                'listing_id' => $listing->id,
                'old_category_id' => $oldCategory->id,
                'new_category_id' => $newCategoryId,
                'status' => $status,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $listing->update(['category_id' => $newCategoryId]);
        });
    }

    /** @return array{0: string, 1: string, 2: string} */
    private function resolveNewSlug(Listing $listing, Category $oldCategory): array
    {
        $direct = FashionLegacyCategoryMap::directChildMap()[$oldCategory->slug] ?? null;
        if ($direct !== null) {
            if ($this->needsDepartmentOverride($oldCategory->slug)) {
                $dept = $this->inferDepartment($listing);
                if ($dept !== null) {
                    $overridden = $this->applyDepartmentToSlug($direct, $dept);
                    if (isset($this->categoryIdsBySlug[$overridden])) {
                        return [$overridden, 'Mapeo directo con departamento inferido', 'mapped'];
                    }
                }

                return [
                    FashionLegacyCategoryMap::sinClasificarSlug(),
                    'Sin departamento inferible para categoría transversal',
                    'unclassified',
                ];
            }

            return [$direct, 'Mapeo directo', 'mapped'];
        }

        $parent = $oldCategory->parent;
        if ($parent !== null) {
            $parentSlug = $parent->slug;

            if (isset(FashionLegacyCategoryMap::parentDepartmentMap()[$parentSlug])) {
                $deptSlug = FashionLegacyCategoryMap::parentDepartmentMap()[$parentSlug];
                $tipoSlug = Str::slug($oldCategory->name);
                $candidate = "{$deptSlug}-ropa-{$tipoSlug}";
                if (isset($this->categoryIdsBySlug[$candidate])) {
                    return [$candidate, 'Mapeo por nombre bajo departamento', 'mapped'];
                }

                return [
                    FashionLegacyCategoryMap::sinClasificarSlug(),
                    "Hijo legacy sin equivalente: {$oldCategory->slug}",
                    'unclassified',
                ];
            }
        }

        return [
            FashionLegacyCategoryMap::sinClasificarSlug(),
            'Categoría legacy no reconocida',
            'unclassified',
        ];
    }

    private function needsDepartmentOverride(string $slug): bool
    {
        return str_starts_with($slug, 'calzado-')
            || str_starts_with($slug, 'accesorios-')
            || str_starts_with($slug, 'deportiva-');
    }

    private function inferDepartment(Listing $listing): ?string
    {
        $haystack = Str::lower($listing->title.' '.$listing->description);

        $rules = [
            'moda-hombre' => ['hombre', ' masculino', ' caballero', ' varon'],
            'moda-ninos' => ['niño', 'niña', 'nino', 'nina', 'infantil', 'bebe', 'bebé', 'kids'],
            'moda-mujer' => ['mujer', ' femenino', ' dama', ' femenina'],
        ];

        foreach ($rules as $dept => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    return $dept;
                }
            }
        }

        return null;
    }

    private function applyDepartmentToSlug(string $slug, string $deptSlug): string
    {
        return preg_replace('/^moda-(mujer|hombre|ninos)/', $deptSlug, $slug) ?? $slug;
    }

    private function ensureSinClasificarCategory(bool $dryRun): void
    {
        $slug = FashionLegacyCategoryMap::sinClasificarSlug();

        if (isset($this->categoryIdsBySlug[$slug])) {
            return;
        }

        if ($dryRun) {
            return;
        }

        $modaRoot = Category::query()->where('slug', 'moda')->first();
        if ($modaRoot === null) {
            return;
        }

        $category = Category::query()->create([
            'parent_id' => $modaRoot->id,
            'name' => 'Sin clasificar',
            'slug' => $slug,
            'level' => FashionCategoryDefinitions::LEVEL_CATEGORIA,
            'vertical' => FashionCategoryDefinitions::VERTICAL,
            'position' => 99,
            'is_active' => false,
            'sale_mode' => 'marketplace',
        ]);

        $this->categoryIdsBySlug[$slug] = $category->id;
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function unclassifiedReport(): Collection
    {
        return collect(
            DB::table('listing_category_migrations')
                ->where('status', 'unclassified')
                ->join('listings', 'listings.id', '=', 'listing_category_migrations.listing_id')
                ->select([
                    'listings.id as listing_id',
                    'listings.title',
                    'listing_category_migrations.reason',
                    'listing_category_migrations.created_at',
                ])
                ->orderByDesc('listing_category_migrations.created_at')
                ->get()
                ->map(fn ($row) => (array) $row)
        );
    }
}
