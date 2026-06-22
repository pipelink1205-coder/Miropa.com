<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrónica',
                'icon' => '📱',
                'description' => 'Teléfonos, computadores, tablets y accesorios',
                'children' => ['Celulares', 'Computadores', 'Tablets', 'Accesorios', 'Fotografía'],
            ],
            [
                'name' => 'Ropa y Accesorios',
                'icon' => '👕',
                'description' => 'Ropa, zapatos y accesorios de moda',
                'children' => ['Ropa Hombre', 'Ropa Mujer', 'Ropa Niños', 'Zapatos', 'Bolsos'],
            ],
            [
                'name' => 'Hogar y Jardín',
                'icon' => '����',
                'description' => 'Muebles, decoración y artículos para el hogar',
                'children' => ['Muebles', 'Decoración', 'Cocina', 'Jardín', 'Iluminación'],
            ],
            [
                'name' => 'Vehículos',
                'icon' => '🚗',
                'description' => 'Carros, motos y accesorios',
                'children' => ['Carros', 'Motos', 'Bicicletas', 'Repuestos', 'Accesorios'],
            ],
            [
                'name' => 'Deportes',
                'icon' => '⚽',
                'description' => 'Artículos deportivos y fitness',
                'children' => ['Fútbol', 'Gimnasio', 'Ciclismo', 'Natación', 'Artes Marciales'],
            ],
            [
                'name' => 'Juguetes y Bebés',
                'icon' => '🧸',
                'description' => 'Juguetes, ropa de bebé y artículos infantiles',
                'children' => ['Juguetes', 'Ropa Bebé', 'Coches', 'Educación'],
            ],
            [
                'name' => 'Libros y Música',
                'icon' => '📚',
                'description' => 'Libros, música, películas y videojuegos',
                'children' => ['Libros', 'Música', 'Películas', 'Videojuegos'],
            ],
            [
                'name' => 'Herramientas',
                'icon' => '🔧',
                'description' => 'Herramientas y equipos para trabajo',
                'children' => ['Herramientas Manuales', 'Herramientas Eléctricas', 'Construcción'],
            ],
            [
                'name' => 'Mascotas',
                'icon' => '🐾',
                'description' => 'Artículos para mascotas',
                'children' => ['Perros', 'Gatos', 'Aves', 'Acuarios'],
            ],
            [
                'name' => 'Arte y Antigüedades',
                'icon' => '🎨',
                'description' => 'Arte, coleccionables y antigüedades',
                'children' => ['Pinturas', 'Esculturas', 'Coleccionables', 'Antigüedades'],
            ],
        ];

        foreach ($categories as $position => $data) {
            $parent = Category::create([
                'parent_id' => null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'icon' => $data['icon'],
                'description' => $data['description'],
                'position' => $position + 1,
                'is_active' => true,
            ]);

            foreach ($data['children'] as $childPos => $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($data['name'].' '.$childName),
                    'icon' => null,
                    'description' => null,
                    'position' => $childPos + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
