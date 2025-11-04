<?php

namespace App\Http\Requests\RAB;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AhspSourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $ahspSourceId = $this->route('id') ?? $this->route('ahsp_source');
        
        return match($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules($ahspSourceId),
            default => [],
        };
    }

    /**
     * Validation rules for creating AHSP source
     */
    protected function createRules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                'unique:ahsp_sources,code',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'is_active' => [
                'boolean',
            ],
            'sort_order' => [
                'integer',
                'min:0',
                'max:999',
            ],
        ];
    }

    /**
     * Validation rules for updating AHSP source
     */
    protected function updateRules($id): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('ahsp_sources', 'code')->ignore($id),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'is_active' => [
                'boolean',
            ],
            'sort_order' => [
                'integer',
                'min:0',
                'max:999',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'code' => 'Kode',
            'name' => 'Nama',
            'description' => 'Deskripsi',
            'icon' => 'Icon',
            'color' => 'Warna',
            'is_active' => 'Status Aktif',
            'sort_order' => 'Urutan',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode wajib diisi.',
            'code.max' => 'Kode maksimal 20 karakter.',
            'code.regex' => 'Kode hanya boleh mengandung huruf kapital, angka, underscore, dan dash.',
            'code.unique' => 'Kode sudah digunakan.',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'color.regex' => 'Format warna harus berupa kode hex (#FFFFFF).',
            'sort_order.integer' => 'Urutan harus berupa angka.',
            'sort_order.min' => 'Urutan minimal 0.',
            'sort_order.max' => 'Urutan maksimal 999.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        if ($this->isMethod('POST')) {
            $this->merge([
                'is_active' => $this->is_active ?? true,
                'sort_order' => $this->sort_order ?? 0,
            ]);
        }

        // Uppercase the code
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }
    }
}
