<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTransactionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('updateStatus', $this->route('transaction'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:paid,shipped,delivered,completed,cancelled'],
        ];
    }
}
