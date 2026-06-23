<?php

namespace Database\Seeders;

use App\Models\Universe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UniverseSeeder extends Seeder
{
    public function run(): void
    {
        $universes = [
            ['name' => 'Vintage', 'accent_color' => '#92400e', 'description' => 'Prendas retro o de época. Para looks únicos con personalidad e historia.'],
            ['name' => 'Premium', 'accent_color' => '#1e293b', 'description' => 'Marcas selectas y mejor estado. Calidad superior sin pagar precio de tienda nueva.'],
            ['name' => 'Streetwear', 'accent_color' => '#c2410c', 'description' => 'Estilo urbano: oversize, sneakers y tendencia callejera.'],
            ['name' => 'Trabajo', 'accent_color' => '#0f766e', 'description' => 'Oficina y smart casual: blazers, camisas y looks listos para trabajar.'],
            ['name' => 'Tallas inclusivas', 'accent_color' => '#7c3aed', 'description' => 'Tallas XL en adelante y fit amplio. Moda cómoda para más cuerpos.'],
            ['name' => 'Eco-impacto', 'accent_color' => '#15803d', 'description' => 'Segunda mano con mayor ahorro ambiental estimado en agua y CO₂.'],
            ['name' => 'Lotes', 'accent_color' => '#0369a1', 'description' => 'Varios artículos en un solo anuncio. Ideal para bebé, niños o liquidar clóset.'],
        ];

        foreach ($universes as $position => $data) {
            Universe::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'accent_color' => $data['accent_color'],
                    'description' => $data['description'],
                    'position' => $position + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
