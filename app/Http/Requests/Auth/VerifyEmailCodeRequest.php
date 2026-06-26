<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:'.config('mail.verification.code_length', 6)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.size' => 'El código debe tener '.config('mail.verification.code_length', 6).' dígitos.',
        ];
    }
}
