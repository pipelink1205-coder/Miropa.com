<?php

namespace App\Support;

/**
 * Taxonomía jerárquica de Moda (GoTrendier-style).
 * Niveles: departamento → segmento (solo Niños) → categoría → tipo.
 *
 * @phpstan-type CategoryNode array{
 *     name: string,
 *     level: string,
 *     children?: list<CategoryNode>
 * }
 */
use Illuminate\Support\Str;

class FashionCategoryDefinitions
{
    public const VERTICAL = 'moda';

    public const LEVEL_DEPARTAMENTO = 'departamento';

    public const LEVEL_SEGMENTO = 'segmento';

    public const LEVEL_CATEGORIA = 'categoria';

    public const LEVEL_TIPO = 'tipo';

    /** @return CategoryNode */
    public static function root(): array
    {
        return [
            'name' => 'Moda',
            'level' => 'vertical',
            'children' => [
                self::departamentoMujer(),
                self::departamentoHombre(),
                self::departamentoNinos(),
            ],
        ];
    }

    /** @return list<string> */
    public static function departmentSlugs(): array
    {
        return [
            self::slug(['Moda', 'Mujer']),
            self::slug(['Moda', 'Hombre']),
            self::slug(['Moda', 'Niños']),
        ];
    }

    /** @param list<string> $ancestors */
    public static function slug(array $ancestors, ?string $name = null): string
    {
        $parts = $name !== null ? [...$ancestors, $name] : $ancestors;

        return Str::slug(implode('-', $parts));
    }

    /** @return CategoryNode */
    private static function departamentoMujer(): array
    {
        return [
            'name' => 'Mujer',
            'level' => self::LEVEL_DEPARTAMENTO,
            'children' => [
                self::categoriaConTipos('Ropa', [
                    'Camisetas', 'Blusas', 'Camisas', 'Vestidos', 'Faldas',
                    'Pantalones', 'Jeans', 'Pantalonetas/Shorts', 'Chaquetas/Abrigos',
                    'Ropa interior', 'Pijamas', 'Ropa deportiva',
                ]),
                self::categoriaConTipos('Calzado', [
                    'Tenis', 'Tacones', 'Botas', 'Sandalias', 'Baletas',
                ]),
                self::categoriaConTipos('Bolsos', [
                    'Bolsos', 'Carteras', 'Mochilas', 'Maletas',
                ]),
                self::categoriaConTipos('Accesorios', [
                    'Gafas', 'Relojes', 'Joyería', 'Cinturones', 'Sombreros', 'Bufandas',
                ]),
            ],
        ];
    }

    /** @return CategoryNode */
    private static function departamentoHombre(): array
    {
        return [
            'name' => 'Hombre',
            'level' => self::LEVEL_DEPARTAMENTO,
            'children' => [
                self::categoriaConTipos('Ropa', [
                    'Camisetas', 'Camisas', 'Pantalones', 'Jeans', 'Pantalonetas/Shorts',
                    'Chaquetas', 'Ropa interior', 'Pijamas', 'Ropa deportiva',
                ]),
                self::categoriaConTipos('Calzado', [
                    'Tenis', 'Botas', 'Formales', 'Sandalias',
                ]),
                self::categoriaConTipos('Accesorios', [
                    'Gorras', 'Relojes', 'Gafas', 'Cinturones', 'Billeteras',
                ]),
            ],
        ];
    }

    /** @return CategoryNode */
    public static function departamentoNinos(): array
    {
        return [
            'name' => 'Niños',
            'level' => self::LEVEL_DEPARTAMENTO,
            'children' => [
                self::segmentoBebe(),
                self::segmentoNina(),
                self::segmentoNino(),
            ],
        ];
    }

    /** @return CategoryNode */
    private static function segmentoBebe(): array
    {
        return [
            'name' => 'Bebé',
            'level' => self::LEVEL_SEGMENTO,
            'children' => [
                self::categoriaConTipos('Ropa', [
                    'Bodys', 'Pañaleros', 'Mamelucos', 'Conjuntos', 'Primera puesta',
                ]),
                self::categoriaConTipos('Calzado', ['Botines']),
                self::categoriaConTipos('Accesorios', ['Gorras', 'Baberos', 'Medias']),
            ],
        ];
    }

    /** @return CategoryNode */
    private static function segmentoNina(): array
    {
        return [
            'name' => 'Niña',
            'level' => self::LEVEL_SEGMENTO,
            'children' => [
                self::categoriaConTipos('Ropa', [
                    'Camisetas', 'Blusas', 'Vestidos', 'Faldas', 'Leggings', 'Jeans',
                    'Shorts', 'Buzos', 'Chaquetas', 'Pijamas', 'Ropa de baño',
                ]),
                self::categoriaConTipos('Calzado', [
                    'Tenis', 'Sandalias', 'Botas', 'Baletas', 'Colegiales',
                ]),
                self::categoriaConTipos('Accesorios', [
                    'Accesorios de cabello', 'Gorras', 'Mochilas', 'Medias',
                ]),
                self::categoriaHoja('Conjuntos'),
                self::categoriaHoja('Uniforme/colegio'),
            ],
        ];
    }

    /** @return CategoryNode */
    private static function segmentoNino(): array
    {
        return [
            'name' => 'Niño',
            'level' => self::LEVEL_SEGMENTO,
            'children' => [
                self::categoriaConTipos('Ropa', [
                    'Camisetas', 'Camisas', 'Polos', 'Pantalones', 'Jeans', 'Pantalonetas',
                    'Buzos', 'Chaquetas', 'Pijamas', 'Ropa de baño',
                ]),
                self::categoriaConTipos('Calzado', [
                    'Tenis', 'Sandalias', 'Botas', 'Colegiales',
                ]),
                self::categoriaConTipos('Accesorios', [
                    'Gorras', 'Mochilas', 'Cinturones', 'Medias',
                ]),
                self::categoriaHoja('Conjuntos'),
                self::categoriaHoja('Uniforme/colegio'),
            ],
        ];
    }

    /** @return list<string> */
    public static function ninosSegmentSlugs(): array
    {
        return ['bebe', 'nina', 'nino'];
    }

    /**
     * @param  list<string>  $tipos
     * @return CategoryNode
     */
    private static function categoriaConTipos(string $name, array $tipos): array
    {
        return [
            'name' => $name,
            'level' => self::LEVEL_CATEGORIA,
            'children' => array_map(
                fn (string $tipo) => ['name' => $tipo, 'level' => self::LEVEL_TIPO],
                $tipos,
            ),
        ];
    }

    /** @return CategoryNode */
    private static function categoriaHoja(string $name): array
    {
        return [
            'name' => $name,
            'level' => self::LEVEL_CATEGORIA,
        ];
    }

    /** @return list<string> */
    public static function leafSlugs(): array
    {
        $slugs = [];
        self::collectLeafSlugs(self::root(), [], $slugs);

        return $slugs;
    }

    /**
     * @param  CategoryNode  $node
     * @param  list<string>  $ancestors
     * @param  list<string>  $slugs
     */
    private static function collectLeafSlugs(array $node, array $ancestors, array &$slugs): void
    {
        $path = [...$ancestors, $node['name']];
        $children = $node['children'] ?? [];

        if ($children === []) {
            $slugs[] = self::slug($path);

            return;
        }

        foreach ($children as $child) {
            self::collectLeafSlugs($child, $path, $slugs);
        }
    }
}
