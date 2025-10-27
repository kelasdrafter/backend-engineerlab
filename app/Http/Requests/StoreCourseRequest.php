<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
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
            'slug' => 'required|string|max:255|unique:courses',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'whatsapp_group_url' => 'nullable|url',
            'thumbnail_url' => 'required|url',
            'syllabus_url' => 'required|url',
            'is_active' => 'required|boolean',
        ];
    }
}
