<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
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
            'allow_access' => ['nullable', 'string'],
            'client_id' => ['nullable', 'string'],
            'password' => ['nullable', 'string',],
            'uuid_client' => ['nullable', 'string', 'uuid'],
            'motherboard_client' => ['nullable', 'string'],
            'processor_client' => ['nullable', 'string'],
            'client_login' => ['nullable', 'integer'],
            'client_logout' => ['nullable', 'integer'],
            'updated_by' => ['nullable', 'json'],
            'created_by' => ['nullable', 'json'],
            'deleted_by' => ['nullable', 'json'],
        ];
    }
}
