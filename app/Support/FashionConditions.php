<?php

namespace App\Support;

use App\Models\Condition;
use Illuminate\Support\Collection;

class FashionConditions
{
    /** @return list<string> */
    public static function slugs(): array
    {
        return [
            'nuevo-con-etiqueta',
            'como-nuevo',
            'buen-estado',
            'con-detalles',
        ];
    }

    /** @return Collection<int, Condition> */
    public static function forFilter(): Collection
    {
        $rows = Condition::query()
            ->whereIn('slug', self::slugs())
            ->get(['id', 'name', 'slug']);

        if ($rows->count() >= count(self::slugs())) {
            return collect(self::slugs())
                ->map(fn (string $slug) => $rows->firstWhere('slug', $slug))
                ->filter()
                ->values();
        }

        $legacyNames = [
            'Nuevo con etiqueta',
            'Nuevo',
            'Como nuevo',
            'Buen estado',
            'Con detalles',
            'Aceptable',
        ];

        return Condition::query()
            ->whereIn('name', $legacyNames)
            ->orderBy('id')
            ->get(['id', 'name', 'slug'])
            ->unique('name')
            ->values()
            ->take(4);
    }
}
