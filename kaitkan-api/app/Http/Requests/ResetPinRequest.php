<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'whatsapp' => [
                'required',
                'string',
                'regex:/^(08|628|\+628)[0-9]{8,12}$/',
            ],
            'otp' => [
                'required',
                'digits:6',
            ],
            'pin' => [
                'required',
                'digits:6',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp.required' => 'Nomor WhatsApp harus diisi',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid',
            'otp.required' => 'Sandi masuk harus diisi',
            'otp.digits' => 'Sandi masuk harus 6 digit',
            'pin.required' => 'PIN harus diisi',
            'pin.digits' => 'PIN harus 6 digit',
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok',
        ];
    }
}

