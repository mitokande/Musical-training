<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lesson_type' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'in:online,in_person,hybrid'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}
