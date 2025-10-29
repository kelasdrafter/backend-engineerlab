<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterLiveLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint, no auth required
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255',
            'whatsapp' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,13}$/',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.string' => 'Nama harus berupa teks',
            'name.max' => 'Nama maksimal 255 karakter',
            
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'whatsapp.string' => 'Nomor WhatsApp harus berupa teks',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890 atau +6281234567890',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'whatsapp' => 'Nomor WhatsApp',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'meta' => [
                    'message' => 'Validasi gagal',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Prepare the data for validation.
     * Normalize WhatsApp number format
     */
    protected function prepareForValidation()
    {
        if ($this->has('whatsapp')) {
            // Remove spaces, dashes, and other non-numeric characters except +
            $whatsapp = preg_replace('/[^0-9+]/', '', $this->whatsapp);
            
            $this->merge([
                'whatsapp' => $whatsapp,
            ]);
        }
    }
}