<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:insight_categories,id',
            'title' => 'required|string|max:500',
            'content' => 'required|string',
            'media' => 'nullable|array|max:5',
            'media.*' => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,pdf,doc,docx,xls,xlsx,dwg,dxf,zip,rar|max:102400', 
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'title.required' => 'Title is required.',
            'title.min' => 'Title must be at least 10 characters.',
            'title.max' => 'Title cannot exceed 500 characters.',
            'content.required' => 'Content is required.',
            'content.min' => 'Content must be at least 100 characters.',
            
            'media.array' => 'Media must be an array of files.',
            'media.max' => 'You can upload maximum 5 files.',
            'media.*.file' => 'Each media item must be a file.',
            'media.*.mimes' => 'File must be: jpg, jpeg, png, gif, webp, mp4, pdf, doc, docx, xls, xlsx, dwg, dxf, zip, or rar.',
            'media.*.max' => 'Each file cannot exceed 100MB.',
        ];
    }
}