<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpactFactor extends Model
{
    protected $fillable = [
        'product_type',
        'water_liters',
        'co2_kg',
    ];

    protected function casts(): array
    {
        return [
            'water_liters' => 'decimal:2',
            'co2_kg' => 'decimal:3',
        ];
    }
}
