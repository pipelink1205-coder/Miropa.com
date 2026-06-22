<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTrustTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_account_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'verification_level' => 'phone',
        ]);

        $this->actingAs($user)
            ->get('/cuenta')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Index')
                ->has('trust', fn ($trust) => $trust
                    ->where('email_verified', true)
                    ->where('phone_verified', true)
                    ->etc()
                )
            );
    }

    public function test_trust_summary_separates_verifications_from_ratings(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified_at' => null,
            'verification_level' => 'email',
        ]);

        $summary = $user->trustSummary();

        $this->assertTrue($summary['email_verified']);
        $this->assertFalse($summary['phone_verified']);
        $this->assertSame(0.0, $summary['rating_avg']);
    }
}
