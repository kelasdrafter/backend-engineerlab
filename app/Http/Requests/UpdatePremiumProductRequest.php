<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePremiumProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin can update products
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'thumbnail_url' => 'sometimes|string|url',
            'file_url' => 'sometimes|string|url',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Product name must be a string',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price cannot be negative',
            'discount_price.numeric' => 'Discount price must be a number',
            'thumbnail_url.url' => 'Thumbnail URL must be a valid URL',
            'file_url.url' => 'File URL must be a valid URL',
        ];
    }
}