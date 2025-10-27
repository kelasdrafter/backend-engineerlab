<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated to check voucher
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string',
            'course_id' => 'nullable|exists:courses,id|required_without:premium_product_id',
            'premium_product_id' => 'nullable|exists:premium_products,id|required_without:course_id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Voucher code is required',
            'course_id.exists' => 'Selected course does not exist',
            'premium_product_id.exists' => 'Selected product does not exist',
            'course_id.required_without' => 'Either course_id or premium_product_id is required',
            'premium_product_id.required_without' => 'Either course_id or premium_product_id is required',
        ];
    }
}