<?php

namespace Tests\Feature;

use App\Http\Resources\ListingResource;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\Profile;
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

    private function tradeEligibleUser(): User
    {
        $user = User::factory()->verified()->create();

        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(60),
            'sales_count' => 2,
            'purchases_count' => 0,
            'ratings_count' => 0,
            'rating_avg' => 0,
        ]);

        return $user->fresh('profile');
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
        $user = $this->tradeEligibleUser();
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
        $user = $this->tradeEligibleUser();
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
        $user = $this->tradeEligibleUser();
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

    public function test_ineligible_owner_cannot_enable_accepts_trade(): void
    {
        $user = User::factory()->phoneVerified()->create();
        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(5),
            'sales_count' => 0,
            'purchases_count' => 0,
        ]);

        $category = Category::factory()->create(['sale_mode' => 'marketplace']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', $this->listingPayload(
                $category,
                $this->eligibleCondition(),
                true,
            ))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['accepts_trade']);
    }
}
