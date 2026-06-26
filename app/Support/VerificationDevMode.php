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
        return app()->environment('local', 'testing')
            && config('sms.driver', 'log') === 'log';
    }
}
