<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marca de la aplicación (mi ropa.com)
    |--------------------------------------------------------------------------
    |
    | Cuando tengas el logo listo, colócalo en public/images/brand/ y define
    | APP_LOGO en .env (ej: images/brand/logo.png).
    |
    | Favicon: reemplaza public/favicon.svg o define APP_FAVICON en .env.
    |
    */

    'name' => env('APP_NAME', 'Mi Ropa'),

    'domain' => env('APP_DOMAIN', 'miropa.com'),

    'tagline' => env('APP_TAGLINE', 'Compra y vende ropa de segunda mano con confianza.'),

    'logo' => env('APP_LOGO'),

    'favicon' => env('APP_FAVICON', 'favicon.svg'),

];
