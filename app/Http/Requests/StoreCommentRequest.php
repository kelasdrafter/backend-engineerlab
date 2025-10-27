<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'insight_id' => 'required|exists:insights,id',
            'parent_id' => 'nullable|exists:insight_comments,id',
            'comment' => 'required|string|max:5000|min:1',
            'mentioned_user_ids' => 'nullable|array',
            'mentioned_user_ids.*' => 'exists:users,id',
            
            // Media validation
            'media' => 'nullable|array|max:5',
            'media.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,webp,mp4,mpeg,mov,avi,webm,pdf,zip,rar',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'insight_id.required' => 'Insight is required.',
            'insight_id.exists' => 'Selected insight does not exist.',
            'parent_id.exists' => 'Parent comment does not exist.',
            'comment.required' => 'Comment is required.',
            'comment.min' => 'Comment cannot be empty.',
            'comment.max' => 'Comment cannot exceed 5000 characters.',
            'mentioned_user_ids.array' => 'Mentioned users must be an array.',
            'mentioned_user_ids.*.exists' => 'One or more mentioned users do not exist.',
            
            // Media messages
            'media.array' => 'Media must be an array.',
            'media.max' => 'Maximum 5 files allowed.',
            'media.*.file' => 'Each media item must be a file.',
            'media.*.max' => 'Each file must not exceed 10MB.',
            'media.*.mimes' => 'Invalid file format. Allowed: images, videos, PDF, ZIP, RAR.',
        ];
    }
}