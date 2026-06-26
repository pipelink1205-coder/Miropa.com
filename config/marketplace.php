<?php

use App\Support\CategoryDefinitions;

return [

    'fashion_category_slugs' => CategoryDefinitions::fashionSlugs(),

    /*
    |--------------------------------------------------------------------------
    | Funcionalidades (activar en producción cuando estén listas)
    |--------------------------------------------------------------------------
    */
    'checkout_enabled' => env('MARKETPLACE_CHECKOUT_ENABLED', false),

    'commission' => [
        'marketplace' => 0.05,
    ],

];
