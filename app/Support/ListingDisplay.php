<?php

namespace App\Support;

use App\Models\Listing;

class ListingDisplay
{
    /** @var array<string, string> */
    private const ATTRIBUTE_LABELS = [
        'size_label' => 'Talla en etiqueta',
        'size_fits_as' => 'Queda como',
        'bust_cm' => 'Pecho',
        'waist_cm' => 'Cintura',
        'length_cm' => 'Largo',
        'sole_length_cm' => 'Longitud plantilla',
    ];

    /** @var array<string, string> */
    private const ATTRIBUTE_SUFFIXES = [
        'bust_cm' => ' cm',
        'waist_cm' => ' cm',
        'length_cm' => ' cm',
        'sole_length_cm' => ' cm',
    ];

    /**
     * Filas legibles para la ficha del anuncio (columnas + atributos extra).
     *
     * @return list<array{label: string, value: string}>
     */
    public static function detailRows(Listing $listing): array
    {
        $rows = [];

        if ($listing->brand?->name) {
            $rows[] = ['label' => 'Marca', 'value' => $listing->brand->name];
        }

        if ($listing->size) {
            $size = $listing->size;
            if ($listing->size_note) {
                $size .= ' · ref. '.$listing->size_note;
            }
            $rows[] = ['label' => 'Talla', 'value' => $size];
        }

        if ($listing->color) {
            $rows[] = ['label' => 'Color', 'value' => $listing->color];
        }

        if ($listing->listing_type === 'lote') {
            $count = $listing->items_count > 1 ? $listing->items_count : null;
            $rows[] = [
                'label' => 'Tipo',
                'value' => $count ? "Lote ({$count} prendas)" : 'Lote / paquete',
            ];
        }

        if ($listing->listing_mode) {
            $rows[] = [
                'label' => 'Modo de venta',
                'value' => $listing->listing_mode === 'compra_protegida'
                    ? 'Compra protegida'
                    : 'Trato directo',
            ];
        }

        foreach ($listing->attributes as $attr) {
            $label = self::ATTRIBUTE_LABELS[$attr->attribute_key] ?? str($attr->attribute_key)->replace('_', ' ')->title()->toString();
            $suffix = self::ATTRIBUTE_SUFFIXES[$attr->attribute_key] ?? '';
            $rows[] = [
                'label' => $label,
                'value' => $attr->attribute_value.$suffix,
            ];
        }

        return $rows;
    }

    /** @return array<string, string> */
    public static function attributesMap(Listing $listing): array
    {
        return $listing->attributes
            ->mapWithKeys(fn ($attr) => [
                self::ATTRIBUTE_LABELS[$attr->attribute_key] ?? $attr->attribute_key => $attr->attribute_value
                    .(self::ATTRIBUTE_SUFFIXES[$attr->attribute_key] ?? ''),
            ])
            ->all();
    }

    /** @return array<string, mixed> */
    public static function forShowPage(Listing $listing): array
    {
        $images = $listing->images->map(fn ($img) => [
            'id' => $img->id,
            'url' => asset('storage/'.$img->path),
            'is_primary' => $img->is_primary,
        ])->values()->all();

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'description' => $listing->description,
            'price' => (float) $listing->price,
            'price_formatted' => $listing->price_formatted,
            'is_negotiable' => $listing->is_negotiable,
            'status' => $listing->status,
            'views_count' => $listing->views_count,
            'published_at' => $listing->published_at?->diffForHumans(),
            'size' => $listing->size,
            'size_note' => $listing->size_note,
            'color' => $listing->color,
            'listing_mode' => $listing->listing_mode,
            'listing_type' => $listing->listing_type,
            'items_count' => $listing->items_count,
            'images' => $images,
            'detail_rows' => self::detailRows($listing),
            'second_life_impact' => SecondLifeImpact::forListing($listing),
            'category' => $listing->category ? [
                'id' => $listing->category->id,
                'name' => $listing->category->name,
                'slug' => $listing->category->slug,
                'sale_mode' => $listing->category->sale_mode ?? 'marketplace',
            ] : null,
            'condition' => $listing->condition ? [
                'id' => $listing->condition->id,
                'name' => $listing->condition->name,
            ] : null,
            'brand' => $listing->brand ? [
                'id' => $listing->brand->id,
                'name' => $listing->brand->name,
            ] : null,
            'location' => $listing->location ? [
                'name' => $listing->location->name,
                'city' => $listing->location->city,
            ] : null,
        ];
    }
}
