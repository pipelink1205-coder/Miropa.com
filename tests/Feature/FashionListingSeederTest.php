<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingImage;
use Database\Seeders\BrandSeeder;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\FashionCategorySeeder;
use Database\Seeders\FashionListingSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\UniverseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FashionListingSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_fashion_listing_seeder_creates_active_listings_with_images(): void
    {
        Storage::fake('public');

        $this->seed(LocationSeeder::class);
        $this->seed(ConditionSeeder::class);
        $this->seed(BrandSeeder::class);
        $this->seed(FashionCategorySeeder::class);
        $this->seed(UniverseSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(FashionListingSeeder::class);

        $this->assertGreaterThanOrEqual(30, Listing::query()->where('status', 'active')->count());

        $withUniverses = Listing::query()->whereHas('universes')->count();
        $this->assertGreaterThanOrEqual(30, $withUniverses);

        $withFashionFields = Listing::query()
            ->whereNotNull('size')
            ->whereNotNull('color')
            ->whereNotNull('brand_id')
            ->count();

        $this->assertGreaterThanOrEqual(30, $withFashionFields);

        $this->assertGreaterThanOrEqual(30, ListingImage::query()->count());
    }
}
