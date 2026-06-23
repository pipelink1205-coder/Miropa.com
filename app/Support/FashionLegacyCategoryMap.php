<?php

namespace App\Support;

/**
 * Mapeo explícito de slugs legacy (6 categorías planas) → taxonomía jerárquica moda-*.
 */
class FashionLegacyCategoryMap
{
    /** @return array<string, string> slug legacy hijo => slug nuevo (hoja) */
    public static function directChildMap(): array
    {
        return [
            // Mujer
            'mujer-vestidos-y-faldas' => 'moda-mujer-ropa-vestidos',
            'mujer-blusas-y-tops' => 'moda-mujer-ropa-blusas',
            'mujer-chaquetas' => 'moda-mujer-ropa-chaquetas-abrigos',
            'mujer-jeans' => 'moda-mujer-ropa-jeans',
            'mujer-pantalones' => 'moda-mujer-ropa-pantalones',
            'mujer-shorts' => 'moda-mujer-ropa-pantalonetas-shorts',
            'mujer-zapatos' => 'moda-mujer-calzado-tenis',
            'mujer-ropa-interior' => 'moda-mujer-ropa-ropa-interior',

            // Hombre
            'hombre-camisas' => 'moda-hombre-ropa-camisas',
            'hombre-polos' => 'moda-hombre-ropa-camisetas',
            'hombre-camisetas' => 'moda-hombre-ropa-camisetas',
            'hombre-chaquetas' => 'moda-hombre-ropa-chaquetas',
            'hombre-pantalones' => 'moda-hombre-ropa-pantalones',
            'hombre-jeans' => 'moda-hombre-ropa-jeans',
            'hombre-tenis' => 'moda-hombre-calzado-tenis',
            'hombre-ropa-interior' => 'moda-hombre-ropa-ropa-interior',

            // Calzado (sin departamento — se infiere o sin clasificar)
            'calzado-tenis-y-zapatillas' => 'moda-mujer-calzado-tenis',
            'calzado-zapatos-casuales' => 'moda-mujer-calzado-tenis',
            'calzado-zapatos-formales' => 'moda-hombre-calzado-formales',
            'calzado-botas' => 'moda-mujer-calzado-botas',
            'calzado-sandalias' => 'moda-mujer-calzado-sandalias',
            'calzado-tacones' => 'moda-mujer-calzado-tacones',

            // Accesorios
            'accesorios-gorras' => 'moda-hombre-accesorios-gorras',
            'accesorios-relojes' => 'moda-mujer-accesorios-relojes',
            'accesorios-gafas-de-sol' => 'moda-mujer-accesorios-gafas',
            'accesorios-bolsos' => 'moda-mujer-bolsos-bolsos',
            'accesorios-cinturones' => 'moda-mujer-accesorios-cinturones',
            'accesorios-joyeria' => 'moda-mujer-accesorios-joyeria',

            // Deportiva
            'deportiva-ropa-fitness' => 'moda-mujer-ropa-ropa-deportiva',
            'deportiva-tenis-deportivos' => 'moda-mujer-calzado-tenis',
            'deportiva-leggings' => 'moda-mujer-ropa-ropa-deportiva',
            'deportiva-sudaderas' => 'moda-mujer-ropa-ropa-deportiva',
            'deportiva-shorts-deportivos' => 'moda-mujer-ropa-pantalonetas-shorts',
            'deportiva-accesorios-gym' => 'moda-mujer-accesorios-bufandas',

            // Niños
            'ninos-ropa-infantil' => 'moda-ninos-nina-ropa-camisetas',
            'ninos-zapatos-ninos' => 'moda-ninos-nino-calzado-tenis',
            'ninos-bebe-0-24-meses' => 'moda-ninos-bebe-ropa-bodys',
            'ninos-uniformes-escolares' => 'moda-ninos-nino-uniforme-colegio',
            'ninos-juguetes' => 'moda-ninos-nino-accesorios-gorras',
        ];
    }

    /** @return array<string, string> slug padre legacy => slug departamento nuevo */
    public static function parentDepartmentMap(): array
    {
        return [
            'mujer' => 'moda-mujer',
            'hombre' => 'moda-hombre',
            'ninos' => 'moda-ninos',
        ];
    }

    /** @return array<string, array{categoria: string, tipo: string}> */
    public static function crossAxisDefaults(): array
    {
        return [
            'calzado' => ['categoria' => 'calzado', 'tipo' => 'tenis'],
            'accesorios' => ['categoria' => 'accesorios', 'tipo' => 'relojes'],
            'deportiva' => ['categoria' => 'ropa', 'tipo' => 'ropa-deportiva'],
        ];
    }

    public static function sinClasificarSlug(): string
    {
        return 'moda-sin-clasificar';
    }

    /** @return list<string> */
    public static function legacyFashionParentSlugs(): array
    {
        return ['mujer', 'hombre', 'calzado', 'accesorios', 'deportiva', 'ninos'];
    }
}
