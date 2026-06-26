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

    /*
    |--------------------------------------------------------------------------
    | Trueque local (en persona)
    |--------------------------------------------------------------------------
    */
    'trade' => [
        'enabled' => env('MARKETPLACE_TRADE_ENABLED', true),
        'require_identity' => true,
        'min_account_age_days' => 30,
        'min_rating_avg' => 4.0,
        'min_ratings_count' => 3,
        'min_completed_transactions' => 1,
    ],

];
