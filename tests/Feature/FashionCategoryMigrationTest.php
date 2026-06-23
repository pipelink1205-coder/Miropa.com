<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\User;
use App\Support\FashionListingCategoryMigrator;
use Database\Seeders\FashionCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FashionCategoryMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FashionCategorySeeder::class);
    }

    public function test_legacy_listing_is_migrated_to_hierarchical_category(): void
    {
        $oldCategory = Category::query()->where('slug', 'mujer-jeans')->firstOrFail();
        $user = User::factory()->create();
        $condition = Condition::factory()->create();

        $listing = Listing::factory()->create([
            'user_id' => $user->id,
            'category_id' => $oldCategory->id,
            'condition_id' => $condition->id,
        ]);

        $report = (new FashionListingCategoryMigrator)->migrate();

        $this->assertSame(1, $report['mapped']);
        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'category_id' => Category::query()->where('slug', 'moda-mujer-ropa-jeans')->value('id'),
        ]);
        $this->assertDatabaseHas('listing_category_migrations', [
            'listing_id' => $listing->id,
            'status' => 'mapped',
        ]);
    }

    public function test_migration_can_be_rolled_back(): void
    {
        $oldCategory = Category::query()->where('slug', 'hombre-camisetas')->firstOrFail();
        $listing = Listing::factory()->create([
            'category_id' => $oldCategory->id,
            'condition_id' => Condition::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $migrator = new FashionListingCategoryMigrator;
        $migrator->migrate();
        $result = $migrator->rollback();

        $this->assertSame(1, $result['restored']);
        $this->assertSame($oldCategory->id, $listing->fresh()->category_id);
        $this->assertSame(0, DB::table('listing_category_migrations')->count());
    }
}
