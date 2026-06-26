<?php

namespace App\Http\Requests\Listing;

use App\Support\FashionListingRules;
use App\Support\ListingUniverses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'condition_id' => ['required', 'integer', 'exists:conditions,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_negotiable' => ['boolean'],
            'currency' => ['string', 'size:3'],
            'status' => ['in:draft,active'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.key' => ['required_with:attributes', 'string', 'max:50'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:200'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
        ], FashionListingRules::baseRules(), ListingUniverses::rules());
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoryId = $this->integer('category_id') ?: null;

            FashionListingRules::validateFashionFields(
                $validator,
                $categoryId,
                $this->all(),
            );

            ListingUniverses::validateForCategory(
                $validator,
                $categoryId,
                $this->all(),
            );

            FashionListingRules::validateTradeFields(
                $validator,
                $categoryId,
                $this->integer('condition_id') ?: null,
                $this->all(),
            );
        });
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Selecciona una categoría.',
            'condition_id.required' => 'Selecciona el estado del artículo.',
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min' => 'La descripción debe tener al menos 20 caracteres.',
            'price.required' => 'El precio es obligatorio.',
            'price.min' => 'El precio no puede ser negativo.',
            'images.*.image' => 'Cada archivo debe ser una imagen.',
            'images.*.max' => 'Cada imagen no puede pesar más de 5MB.',
        ];
    }
}
