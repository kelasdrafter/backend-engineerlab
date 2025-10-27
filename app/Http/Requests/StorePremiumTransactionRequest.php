<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PremiumProduct;

class StorePremiumTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'premium_product_id' => [
                'required',
                'exists:premium_products,id',
                function ($attribute, $value, $fail) {
                    $product = PremiumProduct::find($value);
                    if ($product && !$product->is_active) {
                        $fail('The selected product is not available.');
                    }
                },
            ],
            'voucher_code' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'premium_product_id.required' => 'Product is required',
            'premium_product_id.exists' => 'Selected product does not exist',
            'voucher_code.string' => 'Voucher code must be a string',
        ];
    }
}