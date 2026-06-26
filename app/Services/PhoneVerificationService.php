<?php

namespace App\Services;

use App\Contracts\SmsSender;
use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\TwilioSmsSender;
use App\Support\VerificationDevMode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    public function __construct(
        private readonly ?SmsSender $sender = null,
    ) {}

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        $countryCode = config('sms.default_country_code', '57');

        if (str_starts_with($digits, $countryCode) && strlen($digits) === 10 + strlen($countryCode)) {
            return '+'.$digits;
        }

        if (strlen($digits) === 10) {
            return '+'.$countryCode.$digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        if (str_starts_with($phone, '+')) {
            return '+'.$digits;
        }

        return '+'.$digits;
    }

    public function sendCode(User $user, string $phone): ?string
    {
        $normalized = $this->normalizePhone($phone);

        if (strlen(preg_replace('/\D+/', '', $normalized) ?? '') < 10) {
            throw ValidationException::withMessages([
                'phone' => 'Ingresa un número de celular válido.',
            ]);
        }

        $code = str_pad(
            (string) random_int(0, (10 ** config('sms.code_length', 6)) - 1),
            config('sms.code_length', 6),
            '0',
            STR_PAD_LEFT
        );

        PhoneVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $normalized,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(config('sms.code_expires_minutes', 10)),
            ]
        );

        $brand = config('brand.name', 'Mi Ropa');
        $this->sender()->send(
            $normalized,
            "Tu código {$brand}: {$code}. Expira en ".config('sms.code_expires_minutes', 10).' minutos.'
        );

        if (VerificationDevMode::exposesSmsCodes()) {
            return $code;
        }

        return null;
    }

    public function verifyCode(User $user, string $code): void
    {
        $verification = PhoneVerification::where('user_id', $user->id)->first();

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

        $user->forceFill([
            'phone' => $verification->phone,
            'phone_verified_at' => now(),
            'is_verified' => true,
            'verification_level' => $this->resolveVerificationLevel($user),
        ])->save();

        $verification->delete();
    }

    private function resolveVerificationLevel(User $user): string
    {
        if ($user->verification_level === 'id_document') {
            return 'id_document';
        }

        return 'phone';
    }

    private function sender(): SmsSender
    {
        if ($this->sender) {
            return $this->sender;
        }

        return match (config('sms.driver', 'log')) {
            'twilio' => new TwilioSmsSender,
            default => new LogSmsSender,
        };
    }
}
