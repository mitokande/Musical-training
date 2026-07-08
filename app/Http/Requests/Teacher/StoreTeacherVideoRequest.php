<?php

namespace App\Http\Requests\Teacher;

use App\Models\TeacherVideo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTeacherVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->filled('url') && TeacherVideo::extractYoutubeId($this->input('url')) === null) {
                    $validator->errors()->add('url', __('teacher.videos.invalid_youtube_url'));
                }
            },
        ];
    }
}
