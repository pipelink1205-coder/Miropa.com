<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class BrandedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_uses_branded_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_password_reset_uses_branded_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Password::sendResetLink(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_verify_email_notification_has_spanish_subject(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $notification = new VerifyEmailNotification;
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Verifica tu correo', $mail->subject);
        $this->assertStringContainsString('Mi Ropa', $mail->subject);
    }
}
