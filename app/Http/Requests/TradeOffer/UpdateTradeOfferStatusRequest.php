<?php

namespace App\Http\Requests\TradeOffer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTradeOfferStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:accepted,rejected,cancelled,completed'],
        ];
    }
}
