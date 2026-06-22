<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver SMS
    |--------------------------------------------------------------------------
    |
    | log   → escribe el código en storage/logs (desarrollo local)
    | twilio → envío real vía Twilio (producción)
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'code_length' => 6,

    'code_expires_minutes' => 10,

    'max_attempts' => 5,

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'default_country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '57'),

];
