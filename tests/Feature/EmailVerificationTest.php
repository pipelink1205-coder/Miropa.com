<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_from_dashboard_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_with_code(): void
    {
        config(['mail.default' => 'log']);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        $devCode = session('dev_code');
        $this->assertNotEmpty($devCode);

        $this->actingAs($user)
            ->post(route('verification.confirm'), ['code' => $devCode])
            ->assertRedirect(route('phone.verify.notice'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_register_sends_email_verification_code(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Ana Test',
            'username' => 'ana_test',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accepts_terms' => true,
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'ana@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmailCodeNotification::class);
    }

    public function test_verify_email_code_notification_has_spanish_subject(): void
    {
        $user = User::factory()->unverified()->create();
        $notification = new VerifyEmailCodeNotification('123456');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('código de verificación', strtolower($mail->subject));
        $this->assertStringContainsString('Mi Ropa', $mail->subject);
    }
}
