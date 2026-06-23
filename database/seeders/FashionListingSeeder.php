<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\Universe;
use App\Models\User;
use App\Support\FashionConditions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Anuncios Moda realistas para staging/demo.
 * Usa el árbol jerárquico moda-* con talla, color, marca e imágenes locales.
 */
class FashionListingSeeder extends Seeder
{
    /** @var array<string, string> slug categoría hoja → imagen departamento */
    private const IMAGE_MAP = [
        'mujer' => 'mujer.jpg',
        'hombre' => 'hombre.jpg',
        'ninos' => 'ninos.jpg',
        'calzado' => 'calzado.jpg',
        'accesorios' => 'accesorios.jpg',
        'bolsos' => 'accesorios.jpg',
        'deportiva' => 'deportiva.jpg',
    ];

    public function run(): void
    {
        $users = User::query()->get();
        if ($users->isEmpty()) {
            $this->command?->warn('FashionListingSeeder: no hay usuarios. Ejecuta UserSeeder primero.');

            return;
        }

        $locations = Location::query()->pluck('id');
        $brands = Brand::query()->where('is_active', true)->get()->keyBy('slug');
        $conditions = FashionConditions::forFilter();
        $colors = collect(config('fashion_colors', []))->pluck('name')->filter(fn ($c) => ! in_array($c, ['Otro'], true))->values();
        $universes = Universe::query()->where('is_active', true)->get();

        $leaves = Category::query()
            ->moda()
            ->where('level', 'tipo')
            ->where('is_active', true)
            ->get(['id', 'name', 'slug']);

        if ($leaves->isEmpty()) {
            $this->command?->warn('FashionListingSeeder: ejecuta FashionCategorySeeder primero.');

            return;
        }

        $catalog = $this->catalog();

        foreach ($catalog as $item) {
            $category = $leaves->firstWhere('slug', $item['category_slug'])
                ?? $leaves->random();

            $brand = isset($item['brand'])
                ? ($brands->get(Str::slug($item['brand'])) ?? $brands->random())
                : $brands->random();

            $condition = $conditions->firstWhere('slug', $item['condition'] ?? 'buen-estado')
                ?? $conditions->random();

            $slug = Str::slug($item['title']).'-'.fake()->unique()->numberBetween(1000, 9999);

            $listing = Listing::query()->create([
                'user_id' => $users->random()->id,
                'category_id' => $category->id,
                'condition_id' => $condition->id,
                'location_id' => $locations->random(),
                'brand_id' => $brand->id,
                'title' => $item['title'],
                'slug' => $slug,
                'description' => $item['description'],
                'price' => $item['price'],
                'size' => $item['size'],
                'size_note' => $item['size_note'] ?? null,
                'color' => $item['color'] ?? $colors->random(),
                'listing_mode' => $item['listing_mode'] ?? 'compra_protegida',
                'listing_type' => 'individual',
                'is_negotiable' => $item['negotiable'] ?? fake()->boolean(30),
                'currency' => 'COP',
                'status' => 'active',
                'views_count' => fake()->numberBetween(12, 420),
                'favorites_count' => fake()->numberBetween(0, 28),
                'published_at' => now()->subDays(fake()->numberBetween(1, 75)),
            ]);

            if (! empty($item['attributes'])) {
                foreach ($item['attributes'] as $key => $value) {
                    $listing->attributes()->create([
                        'attribute_key' => $key,
                        'attribute_value' => (string) $value,
                    ]);
                }
            }

            $this->attachImage($listing, $category->slug);

            $universeIds = $this->resolveUniverseIds($item, $universes);
            if ($universeIds !== []) {
                $listing->universes()->attach($universeIds);
            }
        }

        $this->command?->info('FashionListingSeeder: '.count($catalog).' anuncios Moda creados.');
    }

    private function attachImage(Listing $listing, string $categorySlug): void
    {
        $sourceFile = $this->resolveSourceImage($categorySlug);
        $destPath = "listings/demo/{$listing->id}/".basename($sourceFile);

        Storage::disk('public')->makeDirectory("listings/demo/{$listing->id}");
        File::copy($sourceFile, Storage::disk('public')->path($destPath));

        ListingImage::query()->create([
            'listing_id' => $listing->id,
            'path' => $destPath,
            'position' => 0,
            'is_primary' => true,
        ]);
    }

    private function resolveSourceImage(string $categorySlug): string
    {
        foreach (self::IMAGE_MAP as $needle => $file) {
            if (str_contains($categorySlug, $needle)) {
                $path = public_path("images/categories/{$file}");
                if (File::isFile($path)) {
                    return $path;
                }
            }
        }

        return public_path('images/categories/mujer.jpg');
    }

    /** @param  \Illuminate\Support\Collection<int, Universe>  $universes */
    private function resolveUniverseIds(array $item, $universes): array
    {
        if (empty($item['universes'])) {
            return [];
        }

        return $universes
            ->whereIn('slug', $item['universes'])
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function catalog(): array
    {
        return [
            // Mujer — Ropa
            ['category_slug' => 'moda-mujer-ropa-vestidos', 'brand' => 'Zara', 'title' => 'Vestido floral midi Zara', 'price' => 85000, 'size' => 'M', 'color' => 'Multicolor', 'condition' => 'como-nuevo', 'universes' => ['premium', 'vintage'], 'description' => 'Vestido midi con estampado floral. Usado dos veces, sin manchas. Tela fresca ideal para clima cálido.'],
            ['category_slug' => 'moda-mujer-ropa-jeans', 'brand' => 'Levi\'s', 'title' => 'Jeans Levi\'s 501 talla 28', 'price' => 120000, 'size' => 'M', 'color' => 'Azul', 'condition' => 'buen-estado', 'universes' => ['vintage', 'streetwear'], 'description' => 'Jeans rectos clásicos. Desgaste natural en rodillas. Medidas: cintura 28, largo 30.'],
            ['category_slug' => 'moda-mujer-ropa-camisetas', 'brand' => 'H&M', 'title' => 'Camiseta básica algodón H&M', 'price' => 25000, 'size' => 'S', 'color' => 'Blanco', 'condition' => 'buen-estado', 'universes' => ['eco-impacto'], 'description' => 'Camiseta 100% algodón, corte regular. Sin decoloración.'],
            ['category_slug' => 'moda-mujer-ropa-chaquetas-abrigos', 'brand' => 'Mango', 'title' => 'Blazer oversize Mango', 'price' => 145000, 'size' => 'L', 'color' => 'Beige', 'condition' => 'como-nuevo', 'universes' => ['trabajo', 'premium'], 'description' => 'Blazer estructurado color arena. Perfecto para oficina o salida casual.'],
            ['category_slug' => 'moda-mujer-ropa-faldas', 'brand' => 'Pull&Bear', 'title' => 'Falda plisada Pull&Bear', 'price' => 45000, 'size' => 'S', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['vintage'], 'description' => 'Falda plisada midi. Cintura elástica, muy cómoda.'],
            ['category_slug' => 'moda-mujer-ropa-ropa-deportiva', 'brand' => 'Nike', 'title' => 'Leggings Nike Dri-FIT', 'price' => 65000, 'size' => 'M', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'eco-impacto'], 'description' => 'Leggings deportivos con buen soporte. Sin pelusa ni deformación.'],
            // Mujer — Calzado
            ['category_slug' => 'moda-mujer-calzado-tenis', 'brand' => 'Adidas', 'title' => 'Tenis Adidas Stan Smith', 'price' => 180000, 'size' => '38', 'color' => 'Blanco', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'premium'], 'description' => 'Clásicos blancos con detalle verde. Suela con desgaste leve.', 'attributes' => ['sole_length_cm' => '24.5']],
            ['category_slug' => 'moda-mujer-calzado-tacones', 'brand' => 'Steve Madden', 'title' => 'Tacones negros Steve Madden', 'price' => 95000, 'size' => '37', 'color' => 'Negro', 'condition' => 'como-nuevo', 'universes' => ['trabajo', 'premium'], 'description' => 'Tacones como nuevos, solo tienen una postura. Altura tacón 9 cm.', 'attributes' => ['sole_length_cm' => '24']],
            ['category_slug' => 'moda-mujer-calzado-sandalias', 'brand' => 'Bershka', 'title' => 'Sandalias plataforma Bershka', 'price' => 55000, 'size' => '36', 'color' => 'Beige', 'condition' => 'buen-estado', 'universes' => ['eco-impacto'], 'description' => 'Sandalias de plataforma baja. Ideales para verano.'],
            // Mujer — Bolsos
            ['category_slug' => 'moda-mujer-bolsos-bolsos', 'brand' => 'Mango', 'title' => 'Bolso tote cuero sintético Mango', 'price' => 78000, 'size' => 'Única', 'color' => 'Marrón', 'condition' => 'buen-estado', 'universes' => ['premium', 'trabajo'], 'description' => 'Bolso tote espacioso. Interior limpio, asas en buen estado.'],
            ['category_slug' => 'moda-mujer-bolsos-mochilas', 'brand' => 'Totto', 'title' => 'Mochila Totto urbana negra', 'price' => 62000, 'size' => 'Única', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'trabajo'], 'description' => 'Compartimento para laptop 14". Cierres funcionando perfecto.'],
            // Mujer — Accesorios
            ['category_slug' => 'moda-mujer-accesorios-gafas', 'brand' => 'Otra', 'title' => 'Gafas de sol cat-eye vintage', 'price' => 35000, 'size' => 'Única', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['vintage'], 'description' => 'Montura cat-eye. Incluye estuche.'],
            ['category_slug' => 'moda-mujer-accesorios-joyeria', 'brand' => 'Otra', 'title' => 'Collar dorado capas delicado', 'price' => 28000, 'size' => 'Única', 'color' => 'Amarillo', 'condition' => 'como-nuevo', 'universes' => ['premium'], 'description' => 'Collar de tres capas baño de oro. Sin oxidación.'],

            // Hombre
            ['category_slug' => 'moda-hombre-ropa-camisetas', 'brand' => 'Nike', 'title' => 'Camiseta Nike Dri-FIT negra', 'price' => 42000, 'size' => 'L', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['streetwear'], 'description' => 'Camiseta deportiva transpirable. Logo en buen estado.'],
            ['category_slug' => 'moda-hombre-ropa-camisas', 'brand' => 'Tommy Hilfiger', 'title' => 'Camisa Tommy Hilfiger slim', 'price' => 88000, 'size' => 'M', 'color' => 'Azul', 'condition' => 'como-nuevo', 'universes' => ['trabajo', 'premium'], 'description' => 'Camisa slim fit azul claro. Planchada y lista para usar.'],
            ['category_slug' => 'moda-hombre-ropa-pantalones', 'brand' => 'Levi\'s', 'title' => 'Pantalón chino Levi\'s', 'price' => 72000, 'size' => '32', 'color' => 'Beige', 'condition' => 'buen-estado', 'universes' => ['trabajo'], 'description' => 'Chino recto color khaki. Tela resistente.'],
            ['category_slug' => 'moda-hombre-ropa-chaquetas', 'brand' => 'Gap', 'title' => 'Chaqueta bomber Gap', 'price' => 135000, 'size' => 'L', 'color' => 'Verde', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'vintage'], 'description' => 'Bomber verde oliva. Forro interior intacto.'],
            ['category_slug' => 'moda-hombre-calzado-tenis', 'brand' => 'New Balance', 'title' => 'Tenis New Balance 574 grises', 'price' => 210000, 'size' => '42', 'color' => 'Gris', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'premium'], 'description' => '574 clásicos, muy cómodos. Suela con uso normal.', 'attributes' => ['sole_length_cm' => '27']],
            ['category_slug' => 'moda-hombre-calzado-formales', 'brand' => 'Calvin Klein', 'title' => 'Zapatos formales cuero Calvin Klein', 'price' => 165000, 'size' => '41', 'color' => 'Marrón', 'condition' => 'como-nuevo', 'universes' => ['trabajo', 'premium'], 'description' => 'Oxford en cuero genuino. Usados en una ocasión.'],
            ['category_slug' => 'moda-hombre-accesorios-gorras', 'brand' => 'New Era', 'title' => 'Gorra New Era 59FIFTY', 'price' => 48000, 'size' => 'Única', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['streetwear'], 'description' => 'Gorra ajustada talla 7 1/4. Visera curva.'],
            ['category_slug' => 'moda-hombre-accesorios-cinturones', 'brand' => 'Lacoste', 'title' => 'Cinturón Lacoste reversible', 'price' => 55000, 'size' => 'Única', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['premium', 'trabajo'], 'description' => 'Cinturón reversible negro/marrón. Hebilla metálica.'],

            // Niños — Bebé
            ['category_slug' => 'moda-ninos-bebe-ropa-bodys', 'brand' => 'Otra', 'title' => 'Lote 5 bodys algodón 6-9 meses', 'price' => 35000, 'size' => '6-9 meses', 'color' => 'Multicolor', 'condition' => 'buen-estado', 'universes' => ['lotes', 'eco-impacto'], 'description' => 'Cinco bodys suaves en tonos neutros. Lavados con jabón hipoalergénico.', 'size_note' => '63-68 cm'],
            ['category_slug' => 'moda-ninos-bebe-calzado-botines', 'brand' => 'Otra', 'title' => 'Botines bebé 12-18 meses', 'price' => 28000, 'size' => '12-18 meses', 'color' => 'Beige', 'condition' => 'como-nuevo', 'universes' => ['lotes', 'eco-impacto'], 'description' => 'Botines flexibles, suela antideslizante. Casi sin uso.'],
            // Niña
            ['category_slug' => 'moda-ninos-nina-ropa-vestidos', 'brand' => 'Otra', 'title' => 'Vestido niña flores talla 6', 'price' => 32000, 'size' => '6', 'color' => 'Rosa', 'condition' => 'buen-estado', 'universes' => ['lotes'], 'description' => 'Vestido ligero con flores. Perfecto para fiesta o diario.'],
            ['category_slug' => 'moda-ninos-nina-calzado-tenis', 'brand' => 'Puma', 'title' => 'Tenis Puma niña talla 28', 'price' => 55000, 'size' => '10', 'color' => 'Rosa', 'condition' => 'buen-estado', 'universes' => ['lotes', 'eco-impacto'], 'description' => 'Tenis escolar rosados con velcro. Suela en buen estado.'],
            ['category_slug' => 'moda-ninos-nina-ropa-leggings', 'brand' => 'H&M', 'title' => 'Leggings niña pack x2', 'price' => 22000, 'size' => '8', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['lotes', 'eco-impacto'], 'description' => 'Dos leggings elásticos negro y gris. Talla 8 años.'],
            // Niño
            ['category_slug' => 'moda-ninos-nino-ropa-camisetas', 'brand' => 'Nike', 'title' => 'Camiseta Nike niño talla 10', 'price' => 38000, 'size' => '10', 'color' => 'Azul', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'lotes'], 'description' => 'Camiseta deportiva Dri-FIT. Logo intacto.'],
            ['category_slug' => 'moda-ninos-nino-calzado-tenis', 'brand' => 'Adidas', 'title' => 'Tenis Adidas niño talla 32', 'price' => 68000, 'size' => '12', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['lotes'], 'description' => 'Tenis escolares negros. Velcro fácil para el peque.'],
            ['category_slug' => 'moda-ninos-nino-uniforme-colegio', 'brand' => 'Otra', 'title' => 'Uniforme escolar completo talla 12', 'price' => 75000, 'size' => '12', 'color' => 'Azul', 'condition' => 'buen-estado', 'universes' => ['lotes', 'trabajo'], 'description' => 'Pantalón + camisa azul colegio. Sin roturas.'],

            // Extras variados
            ['category_slug' => 'moda-mujer-ropa-blusas', 'brand' => 'Zara', 'title' => 'Blusa satinada Zara ivory', 'price' => 58000, 'size' => 'S', 'color' => 'Blanco', 'condition' => 'nuevo-con-etiqueta', 'universes' => ['premium', 'eco-impacto'], 'description' => 'Blusa nueva con etiqueta. Nunca usada, compra impulsiva.'],
            ['category_slug' => 'moda-mujer-calzado-botas', 'brand' => 'Vans', 'title' => 'Botas Chelsea Vans', 'price' => 125000, 'size' => '39', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'vintage'], 'description' => 'Botas Chelsea negras unisex. Suela original.'],
            ['category_slug' => 'moda-hombre-ropa-jeans', 'brand' => 'Levi\'s', 'title' => 'Jeans 511 slim Levi\'s', 'price' => 98000, 'size' => '30', 'color' => 'Azul', 'condition' => 'buen-estado', 'universes' => ['streetwear', 'vintage'], 'description' => '511 slim fit azul oscuro. Desgaste natural.'],
            ['category_slug' => 'moda-mujer-accesorios-bufandas', 'brand' => 'Otra', 'title' => 'Bufanda lana oversize', 'price' => 32000, 'size' => 'Única', 'color' => 'Gris', 'condition' => 'como-nuevo', 'universes' => ['vintage', 'eco-impacto'], 'description' => 'Bufanda gruesa de lana. Suave y abrigadora.'],
            ['category_slug' => 'moda-mujer-ropa-pijamas', 'brand' => 'Otra', 'title' => 'Pijama satén two-piece', 'price' => 48000, 'size' => 'M', 'color' => 'Morado', 'condition' => 'como-nuevo', 'universes' => ['premium'], 'description' => 'Set de pijama satén. Short + camiseta.'],
            ['category_slug' => 'moda-hombre-accesorios-relojes', 'brand' => 'Otra', 'title' => 'Reloj casual correa cuero', 'price' => 85000, 'size' => 'Única', 'color' => 'Marrón', 'condition' => 'buen-estado', 'universes' => ['premium', 'vintage'], 'description' => 'Reloj analógico con correa cuero marrón. Funciona perfecto.'],
            ['category_slug' => 'moda-ninos-nina-accesorios-mochilas', 'brand' => 'Totto', 'title' => 'Mochila Totto escolar niña', 'price' => 45000, 'size' => 'Única', 'color' => 'Rosa', 'condition' => 'buen-estado', 'universes' => ['lotes'], 'description' => 'Mochila escolar con ruedas opcionales. Bolsillos intactos.'],
            ['category_slug' => 'moda-mujer-ropa-pantalones', 'brand' => 'Massimo Dutti', 'title' => 'Pantalón wide leg lino', 'price' => 110000, 'size' => 'M', 'color' => 'Beige', 'condition' => 'como-nuevo', 'universes' => ['premium', 'tallas-inclusivas'], 'description' => 'Pantalón palazzo de lino. Corte wide leg muy tendencia.', 'listing_mode' => 'trato_directo', 'negotiable' => true],
            ['category_slug' => 'moda-hombre-calzado-sandalias', 'brand' => 'Otra', 'title' => 'Sandalias cuero hombre', 'price' => 52000, 'size' => '42', 'color' => 'Marrón', 'condition' => 'buen-estado', 'universes' => ['eco-impacto'], 'description' => 'Sandalias tipo Birkenstock. Plantilla cómoda.'],
            ['category_slug' => 'moda-mujer-calzado-baletas', 'brand' => 'Bershka', 'title' => 'Baletas negras Bershka', 'price' => 38000, 'size' => '37', 'color' => 'Negro', 'condition' => 'buen-estado', 'universes' => ['premium', 'trabajo'], 'description' => 'Ballet flats clásicas. Suela flexible.'],
        ];
    }
}
