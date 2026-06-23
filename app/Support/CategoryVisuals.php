<?php

namespace App\Support;

class CategoryVisuals
{
    /** @return list<array{title: string, subtitle: string, image: string, url: string, department: string}> */
    public static function fashion(): array
    {
        return self::mapFashionTiles(config('category_visuals.fashion', []));
    }

    /** @return list<array{title: string, subtitle: string, image: string, url: string, search_category: string}> */
    public static function marketplace(): array
    {
        return self::mapTiles(config('category_visuals.marketplace', []));
    }

    /** @return array<string, array{title: string, subtitle: string, image: string}> */
    public static function fashionByDepartment(): array
    {
        return collect(config('category_visuals.fashion', []))
            ->keyBy('department')
            ->map(fn (array $tile) => [
                'title' => $tile['title'],
                'subtitle' => $tile['subtitle'],
                'image' => self::resolveImage($tile['image']),
            ])
            ->all();
    }

    /** @param list<array{title: string, subtitle: string, department: string, image: string}> $tiles */
    private static function mapFashionTiles(array $tiles): array
    {
        return collect($tiles)->map(fn (array $tile) => [
            'title' => $tile['title'],
            'subtitle' => $tile['subtitle'],
            'department' => $tile['department'],
            'image' => self::resolveImage($tile['image']),
            'url' => route('fashion.department', ['department' => $tile['department']]),
        ])->values()->all();
    }

    /** @param list<array{title: string, subtitle: string, search_category: string, image: string}> $tiles */
    private static function mapTiles(array $tiles): array
    {
        return collect($tiles)->map(fn (array $tile) => [
            'title' => $tile['title'],
            'subtitle' => $tile['subtitle'],
            'search_category' => $tile['search_category'],
            'image' => self::resolveImage($tile['image']),
            'url' => route('search', ['category' => $tile['search_category']]),
        ])->values()->all();
    }

    private static function resolveImage(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
