<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'user_id'      => 'sometimes|exists:users,id',
            'course_id'    => 'sometimes|exists:courses,id',
            'voucher_code' => 'nullable|string',
            'status'       => 'sometimes|string',
            'meta'         => 'sometimes|json',
            'amount'       => 'sometimes|numeric|min:0',
        ];
    }
}
