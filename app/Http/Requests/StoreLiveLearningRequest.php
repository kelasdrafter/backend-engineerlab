<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLiveLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
public function authorize(): bool
{
    // Simple check: user logged in dan role = admin
    return auth()->check() && auth()->user()->role === 'admin';
}

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'thumbnail_url' => 'required|string|url',
            'description' => 'required|string',
            'schedule' => 'required|string|max:255',
            'materials' => 'required|array|min:1',
            'materials.*' => 'required|string|max:500',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0|max:999999999.99',
            'zoom_link' => 'required|string|url',
            'community_group_link' => 'required|string|url',
            'max_participants' => 'nullable|integer|min:1|max:100000',
            'status' => 'required|in:draft,published,completed,cancelled',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul live learning wajib diisi',
            'title.max' => 'Judul maksimal 255 karakter',
            
            'thumbnail_url.required' => 'Thumbnail wajib diupload',
            'thumbnail_url.url' => 'URL thumbnail tidak valid',
            
            'description.required' => 'Deskripsi wajib diisi',
            
            'schedule.required' => 'Jadwal wajib diisi',
            'schedule.max' => 'Jadwal maksimal 255 karakter',
            
            'materials.required' => 'Materials wajib diisi',
            'materials.array' => 'Materials harus berupa array',
            'materials.min' => 'Minimal harus ada 1 poin materi',
            'materials.*.required' => 'Setiap poin materi tidak boleh kosong',
            'materials.*.max' => 'Setiap poin materi maksimal 500 karakter',
            
            'is_paid.boolean' => 'Status pembayaran harus true atau false',
            
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga minimal 0',
            'price.max' => 'Harga terlalu besar',
            
            'zoom_link.required' => 'Link Zoom wajib diisi',
            'zoom_link.url' => 'Format link Zoom tidak valid',
            
            'community_group_link.required' => 'Link grup komunitas wajib diisi',
            'community_group_link.url' => 'Format link grup komunitas tidak valid',
            
            'max_participants.integer' => 'Maksimal peserta harus berupa angka',
            'max_participants.min' => 'Maksimal peserta minimal 1',
            'max_participants.max' => 'Maksimal peserta terlalu besar',
            
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status harus salah satu dari: draft, published, completed, cancelled',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'Judul',
            'thumbnail_url' => 'Thumbnail',
            'description' => 'Deskripsi',
            'schedule' => 'Jadwal',
            'materials' => 'Materi',
            'is_paid' => 'Status Pembayaran',
            'price' => 'Harga',
            'zoom_link' => 'Link Zoom',
            'community_group_link' => 'Link Grup Komunitas',
            'max_participants' => 'Maksimal Peserta',
            'status' => 'Status',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'meta' => [
                    'message' => 'Validasi gagal',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'meta' => [
                    'message' => 'Unauthorized. Admin access required.',
                    'code' => 403,
                ],
            ], 403)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values if not provided
        $data = [];
        
        if (!$this->has('is_paid')) {
            $data['is_paid'] = false;
        }
        
        if (!$this->has('status')) {
            $data['status'] = 'draft';
        }
        
        if (count($data) > 0) {
            $this->merge($data);
        }
    }
}