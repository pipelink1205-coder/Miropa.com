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
            'document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'document_back' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Selecciona el tipo de documento.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document_front.required' => 'Debes adjuntar la foto del frente del documento.',
            'document_front.mimes' => 'El frente debe ser JPG, PNG o PDF.',
            'document_front.max' => 'El archivo del frente no puede superar 5 MB.',
            'document_back.required' => 'Debes adjuntar la foto del reverso del documento.',
            'document_back.mimes' => 'El reverso debe ser JPG, PNG o PDF.',
            'document_back.max' => 'El archivo del reverso no puede superar 5 MB.',
        ];
    }
}
