<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is behind the teacher middleware; ownership is enforced in the
        // controller via the TeacherProfilePolicy.
        return true;
    }

    /** Comma-separated text inputs from the editor become arrays here. */
    protected function prepareForValidation(): void
    {
        $listFields = [
            'lesson_types', 'languages', 'instruments', 'genres',
            'expertise_areas', 'age_groups', 'skill_levels', 'teaching_languages',
        ];

        $converted = [];
        foreach ($listFields as $field) {
            $value = $this->input($field);
            if (is_string($value)) {
                $converted[$field] = array_values(array_filter(array_map('trim', explode(',', $value))));
            }
        }

        if ($converted !== []) {
            $this->merge($converted);
        }
    }

    public function rules(): array
    {
        return [
            // General information
            'headline' => ['nullable', 'string', 'max:160'],
            'expertise' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:10000'],
            'teaching_methodology' => ['nullable', 'string', 'max:10000'],
            'teaching_formats' => ['nullable', 'array'],
            'teaching_formats.*' => ['in:online,in_person,hybrid'],
            'lesson_types' => ['nullable', 'array'],
            'lesson_types.*' => ['string', 'max:100'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'show_email' => ['boolean'],
            'public_phone' => ['nullable', 'string', 'max:30'],
            'show_phone' => ['boolean'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.tiktok' => ['nullable', 'url', 'max:255'],
            'social_links.youtube' => ['nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],

            // Music profile
            'primary_instrument' => ['nullable', 'string', 'max:100'],
            'instruments' => ['nullable', 'array', 'max:20'],
            'instruments.*' => ['string', 'max:100'],
            'education_status' => ['nullable', 'string', 'max:100'],
            'educations' => ['nullable', 'array', 'max:20'],
            'educations.*.institution' => ['required_with:educations.*', 'string', 'max:255'],
            'educations.*.program' => ['nullable', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.graduation_year' => ['nullable', 'integer', 'min:1940', 'max:2100'],
            'certificates' => ['nullable', 'string', 'max:5000'],
            'workshops' => ['nullable', 'string', 'max:5000'],
            'masterclasses' => ['nullable', 'string', 'max:5000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'teaching_experience' => ['nullable', 'string', 'max:10000'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['string', 'max:100'],
            'expertise_areas' => ['nullable', 'array'],
            'expertise_areas.*' => ['string', 'max:100'],
            'age_groups' => ['nullable', 'array'],
            'age_groups.*' => ['string', 'max:100'],
            'skill_levels' => ['nullable', 'array'],
            'skill_levels.*' => ['string', 'max:100'],
            'teaching_languages' => ['nullable', 'array'],
            'teaching_languages.*' => ['string', 'max:50'],

            // SEO
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
