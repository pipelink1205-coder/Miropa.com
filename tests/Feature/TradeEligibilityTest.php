<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use App\Services\TradeEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeEligibilityTest extends TestCase
{
    use RefreshDatabase;

    private TradeEligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TradeEligibilityService::class);
        config(['marketplace.trade.enabled' => true]);
    }

    public function test_eligible_user_with_sales_history(): void
    {
        $user = User::factory()->verified()->create();
        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(45),
            'sales_count' => 1,
            'purchases_count' => 0,
            'ratings_count' => 0,
        ]);

        $this->assertTrue($this->service->isEligible($user->fresh('profile')));
    }

    public function test_eligible_user_with_rating_history(): void
    {
        $user = User::factory()->verified()->create();
        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(45),
            'sales_count' => 0,
            'purchases_count' => 0,
            'ratings_count' => 4,
            'rating_avg' => 4.5,
        ]);

        $this->assertTrue($this->service->isEligible($user->fresh('profile')));
    }

    public function test_new_user_without_identity_is_not_eligible(): void
    {
        $user = User::factory()->phoneVerified()->create();
        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(45),
            'sales_count' => 5,
        ]);

        $this->assertFalse($this->service->isEligible($user->fresh('profile')));
        $this->assertSame(
            'Verifica tu identidad con documento para usar trueque.',
            $this->service->failureReason($user->fresh('profile')),
        );
    }

    public function test_user_without_history_is_not_eligible(): void
    {
        $user = User::factory()->verified()->create();
        Profile::factory()->create([
            'user_id' => $user->id,
            'member_since' => now()->subDays(45),
            'sales_count' => 0,
            'purchases_count' => 0,
            'ratings_count' => 0,
        ]);

        $this->assertFalse($this->service->isEligible($user->fresh('profile')));
    }
}
