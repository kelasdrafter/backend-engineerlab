<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearnCornerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Sesuaikan dengan permission yang kamu pakai
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_url' => [
                'required',
                'url',
                'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/'
            ],
            'thumbnail_url' => 'nullable|url',
            'level' => 'required|string|max:100', // Admin ketik manual
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul video wajib diisi',
            'title.max' => 'Judul video maksimal 255 karakter',
            'description.required' => 'Deskripsi video wajib diisi',
            'video_url.required' => 'URL YouTube wajib diisi',
            'video_url.url' => 'Format URL YouTube tidak valid',
            'video_url.regex' => 'URL harus dari YouTube (youtube.com atau youtu.be)',
            'thumbnail_url.url' => 'Format URL thumbnail tidak valid',
            'level.required' => 'Level video wajib diisi',
            'level.max' => 'Level maksimal 100 karakter',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default value for is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => false]);
        }

        // Clean up video URL (remove extra spaces)
        if ($this->has('video_url')) {
            $this->merge(['video_url' => trim($this->video_url)]);
        }

        // Clean up level (remove extra spaces & capitalize first letter)
        if ($this->has('level')) {
            $this->merge(['level' => ucfirst(trim($this->level))]);
        }

        // Add created_by audit field
        if (auth()->check()) {
            $this->merge([
                'created_by' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name ?? 'Unknown',
                    'email' => auth()->user()->email ?? 'Unknown',
                ]
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'Judul Video',
            'description' => 'Deskripsi',
            'video_url' => 'URL YouTube',
            'thumbnail_url' => 'URL Thumbnail',
            'level' => 'Level',
            'is_active' => 'Status Aktif',
        ];
    }
}