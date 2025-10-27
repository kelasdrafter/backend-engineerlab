<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
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
            'code' => 'required|string|max:255',
            'type' => 'required|in:Persentase,Fixed',
            'nominal' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'quota' => 'required|integer|min:0',
            'thumbnail_url' => 'required|url',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'is_repeatable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
