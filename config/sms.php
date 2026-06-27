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

    // `?:` evita que un SMS_DRIVER vacío en .env rompa la detección del modo simulado.
    'driver' => env('SMS_DRIVER') ?: 'log',

    /*
    | Forzar mostrar el código en pantalla (producción sin Twilio). null = automático
    | (true cuando driver es log). Usar SMS_EXPOSE_CODE=true si hiciste config:cache
    | con un valor distinto y necesitas el cuadro amarillo temporalmente.
    */
    'expose_code' => env('SMS_EXPOSE_CODE'),

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
