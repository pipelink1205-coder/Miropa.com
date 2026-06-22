<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');

        if (! $this->user() || ! $conversation) {
            return false;
        }

        return $this->user()->id === $conversation->buyer_id
            || $this->user()->id === $conversation->seller_id;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
