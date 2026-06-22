<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_from_dashboard_to_phone_verification(): void
    {
        $user = User::factory()->withoutPhoneVerification()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('phone.verify.notice'));
    }

    public function test_user_can_verify_phone_with_sms_code(): void
    {
        config(['sms.driver' => 'log']);

        $user = User::factory()->withoutPhoneVerification()->create();

        $this->actingAs($user)
            ->post('/telefono/verificar/enviar', ['phone' => '3001234567'])
            ->assertRedirect();

        $devCode = session('dev_code');
        $this->assertNotEmpty($devCode);

        $this->actingAs($user)
            ->post('/telefono/verificar', ['code' => $devCode])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertTrue($user->hasVerifiedPhone());
        $this->assertSame('phone', $user->verification_level);
    }

    public function test_api_blocks_listing_creation_without_phone_verification(): void
    {
        $user = User::factory()->withoutPhoneVerification()->create();
        $category = \App\Models\Category::factory()->create();
        $condition = \App\Models\Condition::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/listings', [
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'title' => 'Camisa azul talla M',
                'description' => 'Camisa en buen estado, poco uso, ideal para oficina.',
                'price' => 45000,
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_api_can_send_and_verify_phone_code(): void
    {
        config(['sms.driver' => 'log']);

        $user = User::factory()->withoutPhoneVerification()->create();

        $send = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/phone/send', ['phone' => '3009876543'])
            ->assertOk();

        $code = $send->json('meta.dev_code');
        $this->assertNotEmpty($code);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/phone/verify', ['code' => $code])
            ->assertOk()
            ->assertJsonPath('data.has_verified_phone', true);
    }
}
