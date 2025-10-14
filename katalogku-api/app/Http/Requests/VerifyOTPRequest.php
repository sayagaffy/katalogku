<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class VerifyOTPRequest extends FormRequest
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
            'otp' => [
                'required',
                'string',
                'digits:6',
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
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
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid',
            'otp.required' => 'Kode OTP harus diisi',
            'otp.digits' => 'Kode OTP harus 6 digit',
            'name.required' => 'Nama harus diisi',
            'name.min' => 'Nama minimal 3 karakter',
            'name.max' => 'Nama maksimal 100 karakter',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 8 karakter',
        ];
    }
}
