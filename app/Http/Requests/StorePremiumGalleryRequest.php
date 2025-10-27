<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePremiumGalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin can add galleries
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image_url' => 'required|string|url',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image_url.required' => 'Image URL is required',
            'image_url.url' => 'Image URL must be a valid URL',
            'sort_order.integer' => 'Sort order must be a number',
            'sort_order.min' => 'Sort order cannot be negative',
        ];
    }
}