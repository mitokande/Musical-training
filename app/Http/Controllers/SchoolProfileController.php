<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $school = $user->school ?? new School();

        return view('school.profile-edit', [
            'user' => $user,
            'school' => $school,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'long_description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'string', 'max:255'],
            'social_links.youtube' => ['nullable', 'string', 'max:255'],
            'social_links.twitter' => ['nullable', 'string', 'max:255'],
            'social_links.linkedin' => ['nullable', 'string', 'max:255'],
            'social_links.facebook' => ['nullable', 'string', 'max:255'],
            'social_links.tiktok' => ['nullable', 'string', 'max:255'],
            'payment_link' => ['nullable', 'url', 'max:500'],
            'programs' => ['nullable', 'array'],
            'programs.*' => ['string', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'student_capacity' => ['nullable', 'integer', 'min:0'],
        ]);

        $school = $request->user()->school;
        if (!$school) {
            $validated['slug'] = Str::slug($validated['name']);
            $count = School::where('slug', 'LIKE', $validated['slug'] . '%')->count();
            if ($count) {
                $validated['slug'] .= '-' . $count;
            }
        }

        $request->user()->school()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return Redirect::route('school.profile.edit')->with('status', 'profile-updated');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $school = $request->user()->school;
        if (!$school) {
            return Redirect::route('school.profile.edit')->with('error', 'Once okul bilgilerini kaydedin.');
        }

        if ($school->logo_url && !str_starts_with($school->logo_url, 'http')) {
            Storage::disk('public')->delete($school->logo_url);
        }

        $path = $request->file('logo')->store('school-logos', 'public');
        $school->update(['logo_url' => $path]);

        return Redirect::route('school.profile.edit')->with('status', 'logo-updated');
    }
}
