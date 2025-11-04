<?php

namespace App\Http\Requests\RAB;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectTemplateRequest extends FormRequest
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
        $templateId = $this->route('id') ?? $this->route('template');
        
        return match($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules($templateId),
            default => [],
        };
    }

    /**
     * Validation rules for creating project template
     */
    protected function createRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regions', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'ahsp_source_id' => [
                'required',
                'integer',
                Rule::exists('ahsp_sources', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'is_global' => [
                'boolean',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Validation rules for updating project template
     */
    protected function updateRules($id): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'region_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('regions', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'ahsp_source_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ahsp_sources', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'is_global' => [
                'boolean',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Template',
            'description' => 'Deskripsi',
            'region_id' => 'Regional',
            'ahsp_source_id' => 'Sumber AHSP',
            'is_global' => 'Global Template',
            'is_active' => 'Status Aktif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama template wajib diisi.',
            'name.max' => 'Nama template maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            'region_id.required' => 'Regional wajib dipilih.',
            'region_id.exists' => 'Regional tidak valid atau tidak aktif.',
            'ahsp_source_id.required' => 'Sumber AHSP wajib dipilih.',
            'ahsp_source_id.exists' => 'Sumber AHSP tidak valid atau tidak aktif.',
            'is_global.boolean' => 'Status global harus berupa boolean.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
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
                'is_global' => $this->is_global ?? false,
                'is_active' => $this->is_active ?? true,
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Only admin can create global templates
            if ($this->is_global === true || $this->is_global === 1) {
                // Check if user has admin role (adjust according to your auth system)
                // Uncomment and adjust this based on your role system:
                /*
                $user = auth()->user();
                if (!$user || !$user->hasRole('admin')) {
                    $validator->errors()->add('is_global', 'Hanya admin yang dapat membuat template global.');
                }
                */
            }

            // Check if user is updating someone else's private template
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                $templateId = $this->route('id') ?? $this->route('template');
                $template = \App\Models\RAB\ProjectTemplate::find($templateId);
                
                if ($template && !$template->is_global && $template->created_by !== auth()->id()) {
                    $validator->errors()->add('id', 'Anda tidak memiliki akses untuk mengubah template ini.');
                }
            }
        });
    }
}
