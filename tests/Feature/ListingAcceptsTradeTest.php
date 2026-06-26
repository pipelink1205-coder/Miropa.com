<?php

namespace Tests\Feature;

use App\Http\Resources\ListingResource;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\ConditionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListingAcceptsTradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConditionSeeder::class);
    }

    private function eligibleCondition(): Condition
    {
        return Condition::query()->where('slug', 'nuevo-con-etiqueta')->firstOrFail();
    }

    private function ineligibleCondition(): Condition
    {
        return Condition::query()->where('slug', 'buen-estado')->firstOrFail();
    }

    private function listingPayload(Category $category, Condition $condition, bool $acceptsTrade): array
    {
        return [
            'category_id' => $category->id,
            'condition_id' => $condition->id,
            'title' => 'Vestido elegante para ocasión',
            'description' => 'Prenda en excelente estado, ideal para eventos especiales y uso diario.',
            'price' => 120_000,
            'status' => 'active',
            'accepts_trade' => $acceptsTrade,
        ];
    }

    public function test_cannot_enable_accepts_trade_with_ineligible_condition(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['sale_mode' => 'marketplace']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->listingPayload(
                $category,
                $this->ineligibleCondition(),
                true,
            ))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['accepts_trade']);
    }

    public function test_can_enable_accepts_trade_with_eligible_condition_and_marketplace_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['sale_mode' => 'marketplace']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->listingPayload(
                $category,
                $this->eligibleCondition(),
                true,
            ))
            ->assertStatus(201)
            ->assertJsonPath('data.accepts_trade', true);

        $this->assertDatabaseHas('listings', [
            'title' => 'Vestido elegante para ocasión',
            'accepts_trade' => true,
        ]);
    }

    public function test_cannot_enable_accepts_trade_on_classified_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->classified()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->listingPayload(
                $category,
                $this->eligibleCondition(),
                true,
            ))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['accepts_trade']);
    }

    public function test_listing_resource_exposes_accepts_trade_flag(): void
    {
        $listing = Listing::factory()
            ->acceptsTrade()
            ->create(['accepts_trade' => true]);

        $payload = (new ListingResource($listing->load('category', 'condition', 'location')))
            ->toArray(Request::create('/api/v1/listings/example', 'GET'));

        $this->assertTrue($payload['accepts_trade']);
    }
}
