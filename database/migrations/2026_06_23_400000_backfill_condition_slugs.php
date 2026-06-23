<?php

use App\Models\Condition;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'Nuevo' => [
                'name' => 'Nuevo con etiqueta',
                'slug' => 'nuevo-con-etiqueta',
                'description' => 'Sin uso, con etiqueta original puesta',
            ],
            'Como nuevo' => [
                'name' => 'Como nuevo',
                'slug' => 'como-nuevo',
                'description' => 'Usado pocas veces, sin defectos visibles',
            ],
            'Buen estado' => [
                'name' => 'Buen estado',
                'slug' => 'buen-estado',
                'description' => 'Uso normal, puede tener marcas leves',
            ],
            'Aceptable' => [
                'name' => 'Con detalles',
                'slug' => 'con-detalles',
                'description' => 'Desgaste visible o detalles que conviene revisar en fotos',
            ],
            'Para repuestos' => [
                'name' => 'Para repuestos',
                'slug' => 'para-repuestos',
                'description' => 'No funciona o funciona parcialmente (marketplace general)',
            ],
        ];

        foreach ($map as $legacyName => $data) {
            $condition = Condition::query()->where('name', $legacyName)->first();

            if ($condition === null) {
                continue;
            }

            $condition->update($data);
        }

        foreach ($map as $data) {
            Condition::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ],
            );
        }
    }

    public function down(): void
    {
        Condition::query()
            ->whereIn('slug', [
                'nuevo-con-etiqueta', 'como-nuevo', 'buen-estado', 'con-detalles', 'para-repuestos',
            ])
            ->update(['slug' => null]);
    }
};
