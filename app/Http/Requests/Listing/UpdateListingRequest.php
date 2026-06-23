<?php

namespace App\Http\Requests\Listing;

use App\Support\FashionListingRules;
use App\Support\ListingUniverses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('listing'));
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'condition_id' => ['sometimes', 'integer', 'exists:conditions,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'title' => ['sometimes', 'string', 'min:5', 'max:120'],
            'description' => ['sometimes', 'string', 'min:20', 'max:5000'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'is_negotiable' => ['boolean'],
            'status' => ['sometimes', 'in:draft,active,paused'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.key' => ['required_with:attributes', 'string', 'max:50'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:200'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => [
                'integer',
                Rule::exists('listing_images', 'id')->where('listing_id', $this->route('listing')->id),
            ],
        ], FashionListingRules::baseRules(), ListingUniverses::rules());
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoryId = $this->integer('category_id')
                ?: $this->route('listing')->category_id;

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
        });
    }
}
