<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVoucherRequest extends FormRequest
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
            'code' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:Persentase,Fixed',
            'nominal' => 'sometimes|numeric|min:0',
            'name' => 'sometimes|string|max:255',
            'quota' => 'sometimes|integer|min:0',
            'thumbnail_url' => 'sometimes|url',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'is_repeatable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
