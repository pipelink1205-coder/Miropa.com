<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Condition;
use App\Models\User;
use App\Services\TradeEligibilityService;
use Illuminate\Validation\Validator;

class FashionListingRules
{
    /** @return array<string, mixed> */
    public static function baseRules(): array
    {
        return [
            'size' => ['nullable', 'string', 'max:32'],
            'color' => ['nullable', 'string', 'max:64'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'brand_name' => ['nullable', 'string', 'max:120'],
            'size_note' => ['nullable', 'string', 'max:32'],
            'size_label' => ['nullable', 'string', 'max:32'],
            'size_fits_as' => ['nullable', 'string', 'max:32'],
            'listing_mode' => ['nullable', 'in:compra_protegida,trato_directo'],
            'listing_type' => ['nullable', 'in:individual,lote'],
            'accepts_trade' => ['sometimes', 'boolean'],
            'measurements' => ['nullable', 'array'],
            'measurements.bust_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'measurements.waist_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'measurements.length_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'measurements.sole_length_cm' => ['nullable', 'numeric', 'min:0', 'max:50'],
        ];
    }

    public static function validateFashionFields(Validator $validator, ?int $categoryId, array $input): void
    {
        if ($categoryId === null) {
            return;
        }

        $category = Category::query()->find($categoryId);

        if ($category === null || ! self::requiresPublishAttributes($category)) {
            return;
        }

        if ($category->level !== FashionCategoryDefinitions::LEVEL_TIPO) {
            $validator->errors()->add('category_id', 'Selecciona el tipo de prenda (último nivel de categoría).');
        }

        $context = FashionPublishContext::forCategory($category);

        if ($context['show_brand'] && ($context['brand_required'] ?? false)) {
            if (empty($input['brand_id']) && empty($input['brand_name'])) {
                $validator->errors()->add('brand_id', 'Indica la marca de la prenda.');
            }
        }

        if ($context['show_color'] && empty($input['color'])) {
            $validator->errors()->add('color', 'Selecciona el color.');
        } elseif (! empty($input['color']) && ! in_array($input['color'], FashionPublishContext::allowedColorNames(), true)) {
            $validator->errors()->add('color', 'Color no válido.');
        }

        $allowedSizes = $context['sizes'] ?? [];
        if ($allowedSizes !== [] && empty($input['size'])) {
            $validator->errors()->add('size', 'Selecciona la talla.');
        } elseif (! empty($input['size']) && $allowedSizes !== [] && ! in_array($input['size'], $allowedSizes, true)) {
            $validator->errors()->add('size', 'Talla no válida para esta categoría.');
        }

        if (($context['show_size_note'] ?? false) && ! empty($input['size_note'])) {
            $heights = config('fashion_sizes.altura_cm', []);
            if ($heights !== [] && ! in_array($input['size_note'], $heights, true)) {
                $validator->errors()->add('size_note', 'Referencia de altura no válida.');
            }
        }

        if (! empty($input['listing_mode']) && ! in_array($input['listing_mode'], ['compra_protegida', 'trato_directo'], true)) {
            $validator->errors()->add('listing_mode', 'Modo de venta no válido.');
        }
    }

    public static function validateTradeFields(
        Validator $validator,
        ?int $categoryId,
        ?int $conditionId,
        array $input,
        ?bool $currentAcceptsTrade = null,
        ?User $owner = null,
    ): void {
        $acceptsTrade = array_key_exists('accepts_trade', $input)
            ? (bool) $input['accepts_trade']
            : $currentAcceptsTrade;

        if (! $acceptsTrade) {
            return;
        }

        if ($categoryId === null || $conditionId === null) {
            $validator->errors()->add(
                'accepts_trade',
                'Selecciona categoría y condición para aceptar trueque.',
            );

            return;
        }

        $category = Category::query()->find($categoryId);

        if ($category === null || ($category->sale_mode ?? 'marketplace') !== 'marketplace') {
            $validator->errors()->add(
                'accepts_trade',
                'Solo puedes aceptar trueque en anuncios de compra en la plataforma.',
            );

            return;
        }

        $condition = Condition::query()->find($conditionId);

        if ($condition === null || ! in_array($condition->slug, FashionConditions::tradeEligibleSlugs(), true)) {
            $validator->errors()->add(
                'accepts_trade',
                'Solo puedes aceptar trueque en prendas nuevas o como nuevas.',
            );

            return;
        }

        if ($owner !== null) {
            $reason = app(TradeEligibilityService::class)->failureReason($owner);

            if ($reason !== null) {
                $validator->errors()->add('accepts_trade', $reason);
            }
        }
    }

    /** Publicación con talla/color/marca obligatoria solo en el árbol jerárquico moda-* */
    public static function requiresPublishAttributes(Category $category): bool
    {
        return str_starts_with($category->slug, 'moda-');
    }
}
