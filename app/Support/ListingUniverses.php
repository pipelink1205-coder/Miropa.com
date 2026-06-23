<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Universe;
use Illuminate\Validation\Validator;

class ListingUniverses
{
    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'universe_ids' => ['nullable', 'array', 'max:3'],
            'universe_ids.*' => ['integer', 'exists:universes,id'],
        ];
    }

    public static function validateForCategory(Validator $validator, ?int $categoryId, array $input): void
    {
        if ($categoryId === null || empty($input['universe_ids'])) {
            return;
        }

        $category = Category::query()->find($categoryId);

        if ($category === null || ! FashionListingRules::requiresPublishAttributes($category)) {
            $validator->errors()->add('universe_ids', 'Los universos solo aplican a anuncios de Moda.');

            return;
        }

        $ids = array_values(array_unique(array_map('intval', $input['universe_ids'])));

        $activeCount = Universe::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->count();

        if ($activeCount !== count($ids)) {
            $validator->errors()->add('universe_ids', 'Uno o más universos no son válidos.');
        }
    }

    /** @param  list<int>  $universeIds */
    public static function sync(Listing $listing, array $universeIds): void
    {
        $listing->loadMissing('category');

        if ($listing->category === null || ! FashionListingRules::requiresPublishAttributes($listing->category)) {
            $listing->universes()->detach();

            return;
        }

        $ids = Universe::query()
            ->whereIn('id', $universeIds)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $listing->universes()->sync($ids);
    }
}
