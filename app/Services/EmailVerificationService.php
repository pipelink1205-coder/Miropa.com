<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public function sendCode(User $user): ?string
    {
        $code = str_pad(
            (string) random_int(0, (10 ** config('mail.verification.code_length', 6)) - 1),
            config('mail.verification.code_length', 6),
            '0',
            STR_PAD_LEFT
        );

        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(config('mail.verification.code_expires_minutes', 10)),
            ]
        );

        $user->notify(new VerifyEmailCodeNotification($code));

        if (config('mail.default') === 'log') {
            return $code;
        }

        return null;
    }

    public function verifyCode(User $user, string $code): void
    {
        $verification = EmailVerification::where('user_id', $user->id)->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => 'Primero solicita un código de verificación.',
            ]);
        }

        if ($verification->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'El código expiró. Solicita uno nuevo.',
            ]);
        }

        if ($verification->hasExceededAttempts()) {
            throw ValidationException::withMessages([
                'code' => 'Demasiados intentos. Solicita un código nuevo.',
            ]);
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'El código es incorrecto.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        $verification->delete();
    }
}
