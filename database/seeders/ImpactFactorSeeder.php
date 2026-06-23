<?php

namespace Database\Seeders;

use App\Models\ImpactFactor;
use Illuminate\Database\Seeder;

class ImpactFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            ['product_type' => 'prenda', 'water_liters' => 2700, 'co2_kg' => 3.3],
            ['product_type' => 'calzado', 'water_liters' => 1400, 'co2_kg' => 2.1],
            ['product_type' => 'bolso', 'water_liters' => 800, 'co2_kg' => 1.2],
            ['product_type' => 'accesorio', 'water_liters' => 400, 'co2_kg' => 0.6],
            ['product_type' => 'default', 'water_liters' => 1500, 'co2_kg' => 2.0],
        ];

        foreach ($factors as $factor) {
            ImpactFactor::query()->updateOrCreate(
                ['product_type' => $factor['product_type']],
                $factor,
            );
        }
    }
}
