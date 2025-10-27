<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'allow_access' => ['sometimes', 'string'],
            'client_id' => ['sometimes', 'string'],
            'password' => ['sometimes', 'string'],
            'uuid_client' => ['sometimes', 'string', 'uuid'],
            'motherboard_client' => ['sometimes', 'string'],
            'processor_client' => ['sometimes', 'string'],
            'client_login' => ['sometimes', 'integer'],
            'client_logout' => ['sometimes', 'integer'],
            'updated_by' => ['nullable', 'json'],
            'created_by' => ['nullable', 'json'],
            'deleted_by' => ['nullable', 'json'],
        ];
    }
}
