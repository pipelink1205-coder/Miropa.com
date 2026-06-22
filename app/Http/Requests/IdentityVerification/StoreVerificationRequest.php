<?php

namespace App\Http\Requests\IdentityVerification;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:cedula,passport,foreign_id'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Selecciona el tipo de documento.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document.required' => 'Debes adjuntar una foto o PDF de tu documento.',
            'document.mimes' => 'El archivo debe ser JPG, PNG o PDF.',
            'document.max' => 'El archivo no puede superar 5 MB.',
        ];
    }
}
