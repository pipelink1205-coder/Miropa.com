<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class BrandedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_uses_code_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmailCodeNotification::class);
    }

    public function test_password_reset_uses_branded_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Password::sendResetLink(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_verify_email_code_notification_has_spanish_subject(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $notification = new VerifyEmailCodeNotification('654321');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('código de verificación', strtolower($mail->subject));
        $this->assertStringContainsString('Mi Ropa', $mail->subject);
    }
}
