<?php

namespace App\Http\Requests\TradeOffer;

use Illuminate\Foundation\Http\FormRequest;

class StoreTradeOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_listing_id' => ['required', 'integer', 'exists:listings,id'],
            'offered_listing_id' => ['required', 'integer', 'exists:listings,id', 'different:target_listing_id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
