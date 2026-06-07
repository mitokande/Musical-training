<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = $user->teacherProfile ?? new TeacherProfile();

        return view('teacher.profile-edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'short_bio' => ['nullable', 'string', 'max:500'],
            'long_bio' => ['nullable', 'string', 'max:5000'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string', 'max:100'],
            'teaching_subjects' => ['nullable', 'array'],
            'teaching_subjects.*' => ['string', 'max:100'],
            'education_background' => ['nullable', 'string', 'max:500'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'currency' => ['nullable', 'string', 'max:3'],
            'lesson_format' => ['nullable', 'in:online,in_person,hybrid'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'string', 'max:255'],
            'social_links.youtube' => ['nullable', 'string', 'max:255'],
            'social_links.twitter' => ['nullable', 'string', 'max:255'],
            'social_links.linkedin' => ['nullable', 'string', 'max:255'],
            'social_links.facebook' => ['nullable', 'string', 'max:255'],
            'social_links.tiktok' => ['nullable', 'string', 'max:255'],
            'payment_link' => ['nullable', 'url', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:50'],
            'accepts_students' => ['boolean'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'public_profile' => ['boolean'],
        ]);

        $validated['accepts_students'] = $request->boolean('accepts_students');
        $validated['public_profile'] = $request->boolean('public_profile');

        $request->user()->teacherProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return Redirect::route('teacher.profile.edit')->with('status', 'profile-updated');
    }
}
