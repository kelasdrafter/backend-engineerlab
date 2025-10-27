<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLessonRequest extends FormRequest
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
            'user_id'   => 'sometimes|exists:users,id',
            'lesson_id' => 'sometimes|exists:lessons,id',
            'course_id' => 'sometimes|exists:courses,id',
            'is_done'    => 'sometimes|boolean',
        ];
    }
}
