<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            ['name' => 'Nuevo', 'description' => 'Artículo sin uso, en empaque original'],
            ['name' => 'Como nuevo', 'description' => 'Usado pocas veces, sin defectos visibles'],
            ['name' => 'Buen estado', 'description' => 'Con uso normal, puede tener marcas leves'],
            ['name' => 'Aceptable', 'description' => 'Funciona bien pero muestra desgaste evidente'],
            ['name' => 'Para repuestos', 'description' => 'No funciona o funciona parcialmente, para partes'],
        ];

        foreach ($conditions as $condition) {
            Condition::firstOrCreate(['name' => $condition['name']], $condition);
        }
    }
}
