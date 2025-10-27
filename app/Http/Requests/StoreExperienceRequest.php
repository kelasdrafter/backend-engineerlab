<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
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
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'job_title' => 'required|string',
            'company_name' => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contract,internship,freelance',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'location' => 'required|string',
        ];
    }
}
