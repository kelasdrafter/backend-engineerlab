<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'bio' => ['nullable', 'string'],
            'phone' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'photo_url' => ['nullable', 'url'],
            'occupation' => ['nullable', 'max:255'],
            'institution' => ['nullable', 'max:255'],
            'password' => ['required', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'photo_url' => ['nullable', 'url'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
