<?php

namespace App\Support;

use Illuminate\Support\Str;

class CategoryDefinitions
{
    /** @return list<array{name: string, description: string, children: list<string>}> */
    public static function fashion(): array
    {
        return [
            [
                'name' => 'Mujer',
                'description' => 'Vestidos, jeans y accesorios',
                'children' => [
                    'Vestidos y faldas',
                    'Blusas y tops',
                    'Chaquetas',
                    'Jeans',
                    'Pantalones',
                    'Shorts',
                    'Zapatos',
                    'Ropa interior',
                ],
            ],
            [
                'name' => 'Hombre',
                'description' => 'Moda casual y formal',
                'children' => [
                    'Camisas',
                    'Polos',
                    'Camisetas',
                    'Chaquetas',
                    'Pantalones',
                    'Jeans',
                    'Tenis',
                    'Ropa interior',
                ],
            ],
            [
                'name' => 'Calzado',
                'description' => 'Tenis, botas y más',
                'children' => [
                    'Tenis y zapatillas',
                    'Zapatos casuales',
                    'Zapatos formales',
                    'Botas',
                    'Sandalias',
                    'Tacones',
                ],
            ],
            [
                'name' => 'Accesorios',
                'description' => 'Complementa tu estilo',
                'children' => [
                    'Gorras',
                    'Relojes',
                    'Gafas de sol',
                    'Bolsos',
                    'Cinturones',
                    'Joyería',
                ],
            ],
            [
                'name' => 'Deportiva',
                'description' => 'Todo para entrenar',
                'children' => [
                    'Ropa fitness',
                    'Tenis deportivos',
                    'Leggings',
                    'Sudaderas',
                    'Shorts deportivos',
                    'Accesorios gym',
                ],
            ],
            [
                'name' => 'Niños',
                'description' => 'Moda para los pequeños',
                'children' => [
                    'Ropa infantil',
                    'Zapatos niños',
                    'Bebé 0-24 meses',
                    'Uniformes escolares',
                    'Juguetes',
                ],
            ],
        ];
    }

    /** @return list<array{name: string, description: string, children: list<string>}> */
    public static function classified(): array
    {
        return [
            [
                'name' => 'Electrónica',
                'description' => 'Solo contacto — sin pago en Mi Ropa',
                'children' => ['Celulares', 'Computadores', 'Tablets', 'Audio', 'Videojuegos', 'Smartwatch', 'Cámaras'],
            ],
            [
                'name' => 'Hogar',
                'description' => 'Muebles y decoración — trato directo',
                'children' => ['Sala', 'Cocina', 'Decoración', 'Baño', 'Dormitorio', 'Iluminación'],
            ],
            [
                'name' => 'Vehículos',
                'description' => 'Carros, motos — trato directo',
                'children' => ['Carros', 'Motos', 'Repuestos', 'Accesorios'],
            ],
            [
                'name' => 'Deportes',
                'description' => 'Fitness y outdoor — trato directo',
                'children' => ['Fitness', 'Fútbol', 'Ciclismo', 'Running', 'Camping'],
            ],
            [
                'name' => 'Mascotas',
                'description' => 'Artículos para mascotas',
                'children' => ['Perros', 'Gatos', 'Alimentos', 'Accesorios', 'Juguetes'],
            ],
            [
                'name' => 'Libros',
                'description' => 'Literatura y más',
                'children' => ['Literatura', 'Infantil', 'Académicos', 'Tecnología', 'Negocios'],
            ],
            [
                'name' => 'Muebles',
                'description' => 'Mobiliario — trato directo',
                'children' => ['Sofás', 'Comedores', 'Escritorios', 'Camas', 'Sillas'],
            ],
            [
                'name' => 'Herramientas',
                'description' => 'Equipos de trabajo',
                'children' => ['Taladros', 'Llaves', 'Destornilladores', 'Electricidad'],
            ],
            [
                'name' => 'Arte',
                'description' => 'Creatividad y decoración',
                'children' => ['Pintura', 'Manualidades', 'Decoración', 'Lienzos'],
            ],
        ];
    }

    /** @return list<string> */
    public static function fashionSlugs(): array
    {
        return array_map(
            fn (array $cat) => Str::slug($cat['name']),
            self::fashion(),
        );
    }

    /** @return array<string, string> */
    public static function parentSlugRenames(): array
    {
        return [
            'ninos-y-bebe' => 'ninos',
            'ropa-deportiva' => 'deportiva',
            'hogar-y-jardin' => 'hogar',
            'arte-y-antiguedades' => 'arte',
            'libros-y-musica' => 'libros',
        ];
    }

    /** Slug antiguo => slug nuevo (hijo preferido) para migrar anuncios existentes */
    public static function listingSlugMap(): array
    {
        return [
            'ropa-y-accesorios-ropa-mujer' => 'mujer-blusas-y-tops',
            'ropa-y-accesorios-ropa-hombre' => 'hombre-camisetas',
            'ropa-y-accesorios-ropa-ninos' => 'ninos-ropa-infantil',
            'ropa-y-accesorios-zapatos' => 'calzado-tenis-y-zapatillas',
            'ropa-y-accesorios-bolsos' => 'accesorios-bolsos',
            'juguetes-y-bebes-ropa-bebe' => 'ninos-bebe-0-24-meses',
            'juguetes-y-bebes-juguetes' => 'ninos-juguetes',
            'ninos-y-bebe-nino' => 'ninos-ropa-infantil',
            'ninos-y-bebe-nina' => 'ninos-ropa-infantil',
            'ninos-y-bebe-bebe-0-24-meses' => 'ninos-bebe-0-24-meses',
            'ropa-deportiva-shorts-deportivos' => 'deportiva-shorts-deportivos',
            'ropa-deportiva-ropa-de-gym' => 'deportiva-ropa-fitness',
            'ropa-deportiva-tops-deportivos' => 'deportiva-leggings',
            'deportes-futbol' => 'deportes-futbol',
            'deportes-gimnasio' => 'deportes-fitness',
            'deportes-ciclismo' => 'deportes-ciclismo',
            'deportes-natacion' => 'deportes-fitness',
            'hogar-y-jardin-muebles' => 'muebles-sofas',
            'hogar-y-jardin-cocina' => 'hogar-cocina',
            'hogar-y-jardin-decoracion' => 'hogar-decoracion',
            'electronica-celulares' => 'electronica-celulares',
            'electronica-computadores' => 'electronica-computadores',
        ];
    }

    /** @return list<string> */
    public static function legacySlugsToDeactivate(): array
    {
        return [
            'ropa-y-accesorios',
            'juguetes-y-bebes',
            'arte-y-antiguedades',
            'libros-y-musica',
            'hogar-y-jardin',
        ];
    }
}
