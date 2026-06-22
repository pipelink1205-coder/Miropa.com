<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Condition;
use App\Models\Listing;
use App\Models\ListingAttribute;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $conditions = Condition::all();
        $locations = Location::all();

        $sampleListings = [
            ['title' => 'iPhone 13 128GB negro', 'price' => 1800000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Apple', 'Almacenamiento' => '128GB', 'Color' => 'Negro']],
            ['title' => 'Macbook Air M1 2021', 'price' => 3500000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Apple', 'RAM' => '8GB', 'Almacenamiento' => '256GB']],
            ['title' => 'Samsung Galaxy A54', 'price' => 950000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Samsung', 'Almacenamiento' => '128GB', 'Color' => 'Blanco']],
            ['title' => 'Sofá 3 puestos gris', 'price' => 650000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Color' => 'Gris', 'Material' => 'Tela', 'Medidas' => '2.10m']],
            ['title' => 'Mesa de comedor 6 sillas', 'price' => 900000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Material' => 'Madera', 'Color' => 'Café']],
            ['title' => 'Bicicleta MTB rin 29', 'price' => 750000, 'cat' => 'Deportes', 'attrs' => ['Talla' => 'M', 'Color' => 'Negro/Rojo', 'Frenos' => 'Disco']],
            ['title' => 'Chaqueta de cuero hombre M', 'price' => 180000, 'cat' => 'Ropa y Accesorios', 'attrs' => ['Talla' => 'M', 'Color' => 'Negro', 'Material' => 'Cuero sintético']],
            ['title' => 'PlayStation 5 con 2 controles', 'price' => 2800000, 'cat' => 'Electrónica', 'attrs' => ['Almacenamiento' => '825GB', 'Incluye' => '2 controles']],
            ['title' => 'Lavadora LG 10kg', 'price' => 1100000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Marca' => 'LG', 'Capacidad' => '10kg', 'Tipo' => 'Carga frontal']],
            ['title' => 'Guitarra acústica Yamaha', 'price' => 320000, 'cat' => 'Libros y Música', 'attrs' => ['Marca' => 'Yamaha', 'Talla' => '4/4']],
            ['title' => 'Libro El Principito — Edición ilustrada', 'price' => 25000, 'cat' => 'Libros y Música', 'attrs' => ['Autor' => 'Antoine de Saint-Exupéry', 'Idioma' => 'Español']],
            ['title' => 'Taladro DeWalt 18V con maleta', 'price' => 280000, 'cat' => 'Herramientas', 'attrs' => ['Marca' => 'DeWalt', 'Voltaje' => '18V']],
            ['title' => 'Silla ergonómica de oficina', 'price' => 420000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Color' => 'Negro', 'Ajustable' => 'Sí']],
            ['title' => 'Monitor LG 24" Full HD', 'price' => 480000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'LG', 'Tamaño' => '24"', 'Resolución' => 'Full HD']],
            ['title' => 'Cámara Canon EOS Rebel T7', 'price' => 1350000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Canon', 'Megapixeles' => '24.1MP', 'Incluye' => 'Lente 18-55mm']],
            ['title' => 'Tenis Nike Air Max 270 talla 42', 'price' => 220000, 'cat' => 'Ropa y Accesorios', 'attrs' => ['Marca' => 'Nike', 'Talla' => '42', 'Color' => 'Blanco/Negro']],
            ['title' => 'Juego de pesas 30kg', 'price' => 150000, 'cat' => 'Deportes', 'attrs' => ['Peso total' => '30kg', 'Material' => 'Hierro fundido']],
            ['title' => 'Kindle Paperwhite 11va gen', 'price' => 380000, 'cat' => 'Electrónica', 'attrs' => ['Almacenamiento' => '8GB', 'Impermeable' => 'Sí']],
            ['title' => 'Comedor redondo 4 puestos', 'price' => 550000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Material' => 'Vidrio templado', 'Diámetro' => '1.20m']],
            ['title' => 'Xbox Series S con mando', 'price' => 1600000, 'cat' => 'Electrónica', 'attrs' => ['Almacenamiento' => '512GB', 'Color' => 'Blanco']],
            ['title' => 'Patines en línea talla 38', 'price' => 120000, 'cat' => 'Deportes', 'attrs' => ['Talla' => '38', 'Ruedas' => '80mm']],
            ['title' => 'Colchón doble Americana 140x190', 'price' => 480000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Tamaño' => 'Doble', 'Medidas' => '140x190cm']],
            ['title' => 'Mochila Totto 30L negra', 'price' => 85000, 'cat' => 'Ropa y Accesorios', 'attrs' => ['Marca' => 'Totto', 'Capacidad' => '30L', 'Color' => 'Negro']],
            ['title' => 'Licuadora Oster de 1.25L', 'price' => 95000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Marca' => 'Oster', 'Capacidad' => '1.25L', 'Velocidades' => '3']],
            ['title' => 'Tablet Samsung Tab A8 32GB', 'price' => 680000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Samsung', 'Almacenamiento' => '32GB', 'Pantalla' => '10.5"']],
            ['title' => 'Nevera Samsung No Frost 300L', 'price' => 1200000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Marca' => 'Samsung', 'Capacidad' => '300L', 'Tipo' => 'No Frost']],
            ['title' => 'Bota de trabajo talla 41', 'price' => 110000, 'cat' => 'Ropa y Accesorios', 'attrs' => ['Talla' => '41', 'Material' => 'Cuero', 'Punta' => 'Acero']],
            ['title' => 'Ventilador de techo 52 pulgadas', 'price' => 160000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Tamaño' => '52"', 'Velocidades' => '3']],
            ['title' => 'Auriculares Sony WH-1000XM4', 'price' => 750000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Sony', 'Cancelación de ruido' => 'Sí', 'Color' => 'Negro']],
            ['title' => 'Triciclo niño 2-5 años', 'price' => 75000, 'cat' => 'Juguetes y Bebés', 'attrs' => ['Edad' => '2-5 años', 'Color' => 'Rojo']],
            ['title' => 'Maletín ejecutivo cuero café', 'price' => 135000, 'cat' => 'Ropa y Accesorios', 'attrs' => ['Color' => 'Café', 'Material' => 'Cuero genuino']],
            ['title' => 'Lote de libros universitarios ingeniería', 'price' => 200000, 'cat' => 'Libros y Música', 'attrs' => ['Cantidad' => '8 libros', 'Área' => 'Ingeniería']],
            ['title' => 'Caña de pescar telescópica 3.6m', 'price' => 65000, 'cat' => 'Deportes', 'attrs' => ['Longitud' => '3.6m', 'Material' => 'Fibra de vidrio']],
            ['title' => 'Consola Nintendo Switch Oled', 'price' => 2100000, 'cat' => 'Electrónica', 'attrs' => ['Almacenamiento' => '64GB', 'Pantalla' => 'OLED 7"']],
            ['title' => 'Impresora HP LaserJet Pro M15w', 'price' => 340000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'HP', 'Tipo' => 'Láser', 'Conectividad' => 'WiFi']],
            ['title' => 'Set de cocina 7 piezas acero inox', 'price' => 180000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Material' => 'Acero inoxidable', 'Piezas' => '7']],
            ['title' => 'Carro Chevrolet Spark 2018', 'price' => 28000000, 'cat' => 'Vehículos', 'attrs' => ['Marca' => 'Chevrolet', 'Año' => '2018', 'Km' => '42000']],
            ['title' => 'Moto Honda CB190 2020', 'price' => 7500000, 'cat' => 'Vehículos', 'attrs' => ['Marca' => 'Honda', 'Año' => '2020', 'Km' => '18000', 'CC' => '190']],
            ['title' => 'Escritorio con cajones negro', 'price' => 280000, 'cat' => 'Hogar y Jardín', 'attrs' => ['Color' => 'Negro', 'Cajones' => '3', 'Medidas' => '1.20x0.60m']],
            ['title' => 'Cámara de seguridad IP Hikvision 4mp', 'price' => 145000, 'cat' => 'Electrónica', 'attrs' => ['Marca' => 'Hikvision', 'Resolución' => '4MP', 'Visión nocturna' => 'Sí']],
        ];

        foreach ($sampleListings as $data) {
            $category = $categories->firstWhere('name', $data['cat'])
                ?? $categories->first();

            $subcategory = $category->children->random() ?? $category;

            $listing = Listing::create([
                'user_id' => $users->random()->id,
                'category_id' => $subcategory->id,
                'condition_id' => $conditions->random()->id,
                'location_id' => $locations->random()->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.rand(1000, 9999),
                'description' => 'Artículo en excelente estado. '.fake()->paragraph(),
                'price' => $data['price'],
                'is_negotiable' => fake()->boolean(35),
                'currency' => 'COP',
                'status' => 'active',
                'views_count' => rand(5, 300),
                'favorites_count' => rand(0, 30),
                'published_at' => now()->subDays(rand(1, 90)),
            ]);

            // Atributos
            foreach ($data['attrs'] as $key => $value) {
                ListingAttribute::create([
                    'listing_id' => $listing->id,
                    'attribute_key' => $key,
                    'attribute_value' => $value,
                ]);
            }

            // Imagen placeholder (path ficticio — en producción vendría de S3)
            ListingImage::create([
                'listing_id' => $listing->id,
                'path' => 'listings/placeholder-'.$listing->id.'.jpg',
                'position' => 0,
                'is_primary' => true,
            ]);
        }
    }
}
