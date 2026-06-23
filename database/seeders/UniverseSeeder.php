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
            ['name' => 'Vintage', 'accent_color' => '#92400e', 'description' => 'Piezas con historia y estilo retro'],
            ['name' => 'Premium', 'accent_color' => '#1e293b', 'description' => 'Marcas y calidad superior'],
            ['name' => 'Streetwear', 'accent_color' => '#c2410c', 'description' => 'Urbano, oversize y tendencia'],
            ['name' => 'Trabajo', 'accent_color' => '#0f766e', 'description' => 'Oficina y smart casual'],
            ['name' => 'Tallas inclusivas', 'accent_color' => '#7c3aed', 'description' => 'Moda para todos los cuerpos'],
            ['name' => 'Eco-impacto', 'accent_color' => '#15803d', 'description' => 'Segunda vida con mayor impacto positivo'],
            ['name' => 'Lotes', 'accent_color' => '#0369a1', 'description' => 'Paquetes de varias prendas — ideal bebé y niños'],
        ];

        foreach ($universes as $position => $data) {
            Universe::query()->firstOrCreate(
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
