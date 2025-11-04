<?php

namespace App\Http\Requests\RAB;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasterAhspRequest extends FormRequest
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
        $masterAhspId = $this->route('id') ?? $this->route('master_ahsp');
        
        return match($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules($masterAhspId),
            default => [],
        };
    }

    /**
     * Validation rules for creating master AHSP
     */
    protected function createRules(): array
    {
        return [
            'ahsp_source_id' => [
                'required',
                'integer',
                Rule::exists('ahsp_sources', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
            ],
            'name' => [
                'required',
                'string',
                'max:500',
            ],
            'unit' => [
                'required',
                'string',
                'max:20',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'boolean',
            ],
            
            // Composition items
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.category' => [
                'required',
                'string',
                'in:material,labor,equipment',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'items.*.coefficient' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.9999',
            ],
        ];
    }

    /**
     * Validation rules for updating master AHSP
     */
    protected function updateRules($id): array
    {
        return [
            'ahsp_source_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('ahsp_sources', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:500',
            ],
            'unit' => [
                'sometimes',
                'required',
                'string',
                'max:20',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'boolean',
            ],
            
            // Composition items (optional on update)
            'items' => [
                'sometimes',
                'array',
                'min:1',
            ],
            'items.*.category' => [
                'required_with:items',
                'string',
                'in:material,labor,equipment',
            ],
            'items.*.item_id' => [
                'required_with:items',
                'integer',
                Rule::exists('items', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'items.*.coefficient' => [
                'required_with:items',
                'numeric',
                'min:0',
                'max:999999.9999',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ahsp_source_id' => 'Sumber AHSP',
            'code' => 'Kode',
            'name' => 'Nama Pekerjaan',
            'unit' => 'Satuan',
            'description' => 'Deskripsi',
            'is_active' => 'Status Aktif',
            'items' => 'Item Komposisi',
            'items.*.category' => 'Kategori Item',
            'items.*.item_id' => 'Item',
            'items.*.coefficient' => 'Koefisien',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ahsp_source_id.required' => 'Sumber AHSP wajib dipilih.',
            'ahsp_source_id.exists' => 'Sumber AHSP tidak valid atau tidak aktif.',
            'code.required' => 'Kode wajib diisi.',
            'code.max' => 'Kode maksimal 50 karakter.',
            'name.required' => 'Nama pekerjaan wajib diisi.',
            'name.max' => 'Nama pekerjaan maksimal 500 karakter.',
            'unit.required' => 'Satuan wajib diisi.',
            'unit.max' => 'Satuan maksimal 20 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
            
            'items.required' => 'Item komposisi wajib diisi minimal 1.',
            'items.array' => 'Item komposisi harus berupa array.',
            'items.min' => 'Item komposisi minimal 1.',
            'items.*.category.required' => 'Kategori item wajib dipilih.',
            'items.*.category.in' => 'Kategori item harus: material, labor, atau equipment.',
            'items.*.item_id.required' => 'Item wajib dipilih.',
            'items.*.item_id.exists' => 'Item tidak valid atau tidak aktif.',
            'items.*.coefficient.required' => 'Koefisien wajib diisi.',
            'items.*.coefficient.numeric' => 'Koefisien harus berupa angka.',
            'items.*.coefficient.min' => 'Koefisien minimal 0.',
            'items.*.coefficient.max' => 'Koefisien maksimal 999999.9999.',
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
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for duplicate code within same AHSP source
            if ($this->has('ahsp_source_id') && $this->has('code')) {
                $query = \App\Models\RAB\MasterAhsp::where('ahsp_source_id', $this->ahsp_source_id)
                    ->where('code', $this->code);
                
                // Exclude current record when updating
                if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                    $id = $this->route('id') ?? $this->route('master_ahsp');
                    $query->where('id', '!=', $id);
                }
                
                if ($query->exists()) {
                    $validator->errors()->add('code', 'Kode sudah digunakan dalam sumber AHSP ini.');
                }
            }

            // Validate item categories match item types
            if ($this->has('items') && is_array($this->items)) {
                foreach ($this->items as $index => $itemData) {
                    if (isset($itemData['item_id']) && isset($itemData['category'])) {
                        $item = \App\Models\RAB\Item::find($itemData['item_id']);
                        
                        if ($item && $item->type !== $itemData['category']) {
                            $validator->errors()->add(
                                "items.{$index}.category",
                                "Kategori tidak sesuai dengan tipe item. Item ini bertipe: {$item->type}."
                            );
                        }
                    }
                }
            }
        });
    }
}
