<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'min:5', 'max:5000'],
            // Honeypot: bots fill hidden fields. Must stay empty.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => __('تم رفض الإرسال.'),
        ];
    }
}
