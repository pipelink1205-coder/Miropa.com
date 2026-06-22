<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:'.config('sms.code_length', 6)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Ingresa el código que recibiste.',
            'code.size' => 'El código debe tener '.config('sms.code_length', 6).' dígitos.',
        ];
    }
}
