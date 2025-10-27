<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'user_id'      => 'required|exists:users,id',
            'course_id'    => 'required|exists:courses,id',
            'voucher_code' => 'nullable|string',
            'status'       => 'required|string',
            'meta'         => 'required|json',
            'amount'       => 'required|numeric|min:0',
        ];
    }
}
