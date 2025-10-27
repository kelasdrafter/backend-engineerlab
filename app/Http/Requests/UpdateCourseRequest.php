<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'privilege' => 'sometimes|nullable|string',
            'benefit' => 'sometimes|nullable|string',
            'description' => 'sometimes|string',
            'short_description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'sometimes|nullable|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'whatsapp_group_url' => 'sometimes|nullable|url',
            'thumbnail_url' => 'sometimes|url',
            'syllabus_url' => 'sometimes|url',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
