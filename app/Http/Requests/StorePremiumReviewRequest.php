<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePremiumReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admin can add reviews (manual/fake reviews)
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reviewer_name' => 'required|string|max:255',
            'reviewer_photo' => 'nullable|string|url',
            'review_text' => 'required|string',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reviewer_name.required' => 'Reviewer name is required',
            'reviewer_photo.url' => 'Reviewer photo must be a valid URL',
            'review_text.required' => 'Review text is required',
        ];
    }
}