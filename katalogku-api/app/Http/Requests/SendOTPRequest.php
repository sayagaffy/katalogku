<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOTPRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'whatsapp' => [
                'required',
                'string',
                'regex:/^(08|628|\+628)[0-9]{8,12}$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.required' => 'Nomor WhatsApp harus diisi',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890',
        ];
    }
}
