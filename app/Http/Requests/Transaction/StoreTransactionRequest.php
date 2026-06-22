<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'exists:listings,id'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,card'],
            'shipping_method' => ['nullable', 'string', 'in:in_person,shipping'],
        ];
    }
}
