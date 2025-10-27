<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'transaction_id' => 'required|string',
            'user_id'        => 'required|exists:users,id',
            'payment_method' => 'required|string',
            'status'         => 'required|string',
            'raw_response'   => 'nullable|json',
            'raw_request'    => 'nullable|json',
        ];
    }
}
