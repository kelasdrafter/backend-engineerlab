<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
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
        'chapter_id' => 'sometimes|exists:chapters,id',
        'name' => 'sometimes|string|max:255',
        'sequence' => 'sometimes|integer',
        'embed_url' => 'sometimes|url',
        'video_url' => 'nullable|url',
        'description' => 'sometimes|string',
        'supporting_file_url' => 'nullable|url',
        'thumbnail_url' => 'nullable|url',
        'is_public' => 'sometimes|boolean',
        'is_active' => 'sometimes|boolean',
        'require_completion' => 'sometimes|boolean',  // ← TAMBAH INI
    ];
}
}
