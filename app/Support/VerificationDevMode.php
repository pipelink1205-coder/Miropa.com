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
        // Con driver "log" no hay SMS real: mostrar el código en pantalla en cualquier entorno
        // (local, staging o producción antes de activar Twilio).
        return config('sms.driver', 'log') === 'log';
    }
}
