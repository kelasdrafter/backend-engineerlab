<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInsightRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|required|exists:insight_categories,id',
            'title' => 'sometimes|required|string|max:500',  
            'content' => 'sometimes|required|string',  
            'media' => 'nullable|array|max:5',
            'media.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,pdf,doc,docx,xls,xlsx,dwg,dxf,zip,rar|max:102400', // 100MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'title.required' => 'Title is required.',
            'title.max' => 'Title cannot exceed 500 characters.',
            'content.required' => 'Content is required.',
            'media.array' => 'Media must be an array of files.',
            'media.max' => 'You can upload maximum 5 files.',
            'media.*.file' => 'Each media item must be a file.',
            'media.*.mimes' => 'File must be: jpg, jpeg, png, gif, webp, mp4, pdf, doc, docx, xls, xlsx, dwg, dxf, zip, or rar.',
            'media.*.max' => 'Each file cannot exceed 100MB.',
        ];
    }
}