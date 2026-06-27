<?php

namespace App\Support;

class VerificationDevMode
{
    public static function exposesEmailCodes(): bool
    {
        return app()->environment('local', 'testing')
            && config('mail.default') === 'log';
    }

    public static function exposesSmsCodes(): bool
    {
        $explicit = config('sms.expose_code');

        if ($explicit !== null && $explicit !== '') {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }

        // Con driver "log" (o vacío/mal seteado en .env) no hay SMS real.
        return self::smsUsesSimulatedDriver();
    }

    private static function smsUsesSimulatedDriver(): bool
    {
        $driver = config('sms.driver', 'log');

        return $driver === 'log' || $driver === null || $driver === '';
    }
}
