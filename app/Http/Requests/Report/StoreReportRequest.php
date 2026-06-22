<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reportable_type' => ['required', 'string', 'in:listing,user,message'],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'in:spam,fraud,inappropriate,prohibited,other'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
