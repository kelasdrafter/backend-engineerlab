<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceRequest extends FormRequest
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
            'user_id' => 'sometimes|integer|exists:users,id',
            'job_title' => 'sometimes|string',
            'company_name' => 'sometimes|string',
            'employment_type' => 'sometimes|in:full_time,part_time,contract,internship,freelance',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'location' => 'sometimes|string',
        ];
    }
}
