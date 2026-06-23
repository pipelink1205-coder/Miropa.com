<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            [
                'name' => 'Nuevo con etiqueta',
                'slug' => 'nuevo-con-etiqueta',
                'description' => 'Sin uso, con etiqueta original puesta',
            ],
            [
                'name' => 'Como nuevo',
                'slug' => 'como-nuevo',
                'description' => 'Usado pocas veces, sin defectos visibles',
            ],
            [
                'name' => 'Buen estado',
                'slug' => 'buen-estado',
                'description' => 'Uso normal, puede tener marcas leves',
            ],
            [
                'name' => 'Con detalles',
                'slug' => 'con-detalles',
                'description' => 'Desgaste visible o detalles que conviene revisar en fotos',
            ],
            [
                'name' => 'Aceptable',
                'slug' => 'aceptable',
                'description' => 'Funciona bien pero muestra desgaste evidente (marketplace general)',
            ],
            [
                'name' => 'Para repuestos',
                'slug' => 'para-repuestos',
                'description' => 'No funciona o funciona parcialmente (marketplace general)',
            ],
        ];

        foreach ($conditions as $condition) {
            Condition::query()->updateOrCreate(
                ['slug' => $condition['slug']],
                [
                    'name' => $condition['name'],
                    'description' => $condition['description'],
                ],
            );
        }
    }
}
