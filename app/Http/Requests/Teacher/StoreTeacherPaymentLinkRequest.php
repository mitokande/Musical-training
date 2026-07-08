<?php

namespace App\Http\Requests\Teacher;

use App\Models\TeacherPaymentLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherPaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:https', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_text' => ['nullable', 'string', 'max:100'],
            'lesson_type' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', Rule::in(TeacherPaymentLink::VISIBILITIES)],
            'is_active' => ['boolean'],
        ];
    }
}
