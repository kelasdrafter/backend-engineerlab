<?php

namespace App\Http\Requests\RAB;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $projectId = $this->route('id') ?? $this->route('project');
        
        return match($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules($projectId),
            default => [],
        };
    }

    /**
     * Validation rules for creating project
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
                'max:5000',
            ],
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regions', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'template_id' => [
                'nullable',
                'integer',
                Rule::exists('project_templates', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'ahsp_source_id' => [
                'required_without:template_id',
                'integer',
                Rule::exists('ahsp_sources', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'overhead_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'profit_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'ppn_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'start_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'status' => [
                'in:draft,active,completed,cancelled',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Validation rules for updating project
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
                'max:5000',
            ],
            'overhead_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'profit_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'ppn_percentage' => [
                'numeric',
                'min:0',
                'max:100',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'status' => [
                'in:draft,active,completed,cancelled',
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
            'name' => 'Nama Project',
            'description' => 'Deskripsi',
            'region_id' => 'Regional',
            'template_id' => 'Template',
            'ahsp_source_id' => 'Sumber AHSP',
            'overhead_percentage' => 'Persentase Overhead',
            'profit_percentage' => 'Persentase Profit',
            'ppn_percentage' => 'Persentase PPN',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'status' => 'Status',
            'is_active' => 'Status Aktif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama project wajib diisi.',
            'name.max' => 'Nama project maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            
            'region_id.required' => 'Regional wajib dipilih.',
            'region_id.exists' => 'Regional tidak valid atau tidak aktif.',
            
            'template_id.exists' => 'Template tidak valid atau tidak aktif.',
            
            'ahsp_source_id.required_without' => 'Sumber AHSP wajib dipilih jika tidak menggunakan template.',
            'ahsp_source_id.exists' => 'Sumber AHSP tidak valid atau tidak aktif.',
            
            'overhead_percentage.numeric' => 'Persentase overhead harus berupa angka.',
            'overhead_percentage.min' => 'Persentase overhead minimal 0%.',
            'overhead_percentage.max' => 'Persentase overhead maksimal 100%.',
            
            'profit_percentage.numeric' => 'Persentase profit harus berupa angka.',
            'profit_percentage.min' => 'Persentase profit minimal 0%.',
            'profit_percentage.max' => 'Persentase profit maksimal 100%.',
            
            'ppn_percentage.numeric' => 'Persentase PPN harus berupa angka.',
            'ppn_percentage.min' => 'Persentase PPN minimal 0%.',
            'ppn_percentage.max' => 'Persentase PPN maksimal 100%.',
            
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            
            'status.in' => 'Status harus: draft, active, completed, atau cancelled.',
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
                'overhead_percentage' => $this->overhead_percentage ?? 10.00,
                'profit_percentage' => $this->profit_percentage ?? 10.00,
                'ppn_percentage' => $this->ppn_percentage ?? 11.00,
                'status' => $this->status ?? 'draft',
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
            // If template is selected, inherit AHSP source from template
            if ($this->has('template_id') && $this->template_id) {
                $template = \App\Models\RAB\ProjectTemplate::find($this->template_id);
                
                if ($template) {
                    // Check if user can use this template
                    if (!$template->is_global && $template->created_by !== auth()->id()) {
                        $validator->errors()->add('template_id', 'Anda tidak memiliki akses ke template ini.');
                    }
                    
                    // If ahsp_source_id is provided and different from template's source
                    if ($this->has('ahsp_source_id') && $this->ahsp_source_id != $template->ahsp_source_id) {
                        $validator->errors()->add(
                            'ahsp_source_id',
                            'Sumber AHSP akan otomatis mengikuti template yang dipilih. Tidak dapat diubah.'
                        );
                    }
                }
            }

            // Prevent changing AHSP source on update
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                if ($this->has('ahsp_source_id')) {
                    $projectId = $this->route('id') ?? $this->route('project');
                    $project = \App\Models\RAB\Project::find($projectId);
                    
                    if ($project && $project->ahsp_source_id != $this->ahsp_source_id) {
                        $validator->errors()->add(
                            'ahsp_source_id',
                            'Sumber AHSP tidak dapat diubah setelah project dibuat.'
                        );
                    }
                }

                // Check ownership before update
                $projectId = $this->route('id') ?? $this->route('project');
                $project = \App\Models\RAB\Project::find($projectId);
                
                if ($project && $project->created_by !== auth()->id()) {
                    $validator->errors()->add('id', 'Anda tidak memiliki akses untuk mengubah project ini.');
                }
            }
        });
    }
}
