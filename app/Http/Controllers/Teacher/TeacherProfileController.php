<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpdateTeacherProfileRequest;
use App\Models\FeedItem;
use App\Models\TeacherProfile;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherProfileModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherProfileModerationService $moderation,
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = $this->profileFor($request);

        $this->authorize('update', $profile);

        return view('teacher.profile-edit', [
            'user' => $user,
            'profile' => $profile->load(['educations', 'instruments', 'services', 'videos', 'media', 'paymentLinks']),
            'capabilities' => $this->capabilities->capabilities($user),
        ]);
    }

    /**
     * Draft save. Repeatable rows (educations, extra instruments) are synced
     * from the submitted arrays; the profile itself keeps its current status.
     */
    public function update(UpdateTeacherProfileRequest $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $this->authorize('update', $profile);

        $validated = $request->validated();
        $educations = $validated['educations'] ?? null;
        $instruments = $validated['instruments'] ?? null;
        unset($validated['educations'], $validated['instruments']);

        $validated['show_email'] = $request->boolean('show_email');
        $validated['show_phone'] = $request->boolean('show_phone');

        DB::transaction(function () use ($profile, $validated, $educations, $instruments) {
            $profile->update($validated);

            if ($educations !== null) {
                $profile->educations()->delete();
                foreach (array_values($educations) as $i => $row) {
                    if (empty($row['institution'])) {
                        continue;
                    }
                    $profile->educations()->create([
                        'institution' => $row['institution'],
                        'program' => $row['program'] ?? null,
                        'field_of_study' => $row['field_of_study'] ?? null,
                        'graduation_year' => $row['graduation_year'] ?? null,
                        'sort_order' => $i,
                    ]);
                }
            }

            if ($instruments !== null) {
                $profile->instruments()->delete();
                foreach (array_values(array_filter($instruments)) as $i => $instrument) {
                    $profile->instruments()->create([
                        'instrument' => $instrument,
                        'is_primary' => false,
                        'sort_order' => $i,
                    ]);
                }
            }
        });

        return back()->with('status', 'profile-updated');
    }

    public function updateCover(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $this->authorize('update', $profile);

        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($profile->cover_image_path) {
            $old = public_path($profile->cover_image_path);
            if (file_exists($old)) {
                @unlink($old);
            }
        }

        // Stored directly under public/ following the avatar convention.
        $filename = $request->file('cover')->hashName();
        $destDir = public_path('images/teacher/covers');
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $request->file('cover')->move($destDir, $filename);

        $profile->update(['cover_image_path' => 'images/teacher/covers/'.$filename]);

        return back()->with('status', 'cover-updated');
    }

    /** Submit the profile for admin review. */
    public function submit(Request $request): RedirectResponse
    {
        $profile = $this->profileFor($request);
        $this->authorize('submit', $profile);

        $missing = $this->missingRequiredFields($profile);
        if ($missing !== []) {
            return back()->with('submit-error', $missing);
        }

        $this->moderation->submit($profile);

        return back()->with('status', 'profile-submitted');
    }

    /** "View as Student" — render the public profile with no edit controls. */
    public function preview(Request $request): View
    {
        $profile = $this->profileFor($request);
        $this->authorize('view', $profile);

        return view('teachers.show', [
            'profile' => $profile->load(['user', 'educations', 'instruments', 'services', 'videos', 'media', 'paymentLinks']),
            'isPreview' => true,
            'articles' => $profile->user->articles()->published()->latest('published_at')->limit(12)->get(),
            'reviews' => collect(),
            'reviewStats' => ['count' => 0, 'average' => null],
            'canReview' => false,
            'myReview' => null,
            'bookingEnabled' => false,
            'bookingDays' => [],
            'feedItems' => FeedItem::with(['actor', 'subject'])
                ->where('user_id', $profile->user_id)
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }

    private function profileFor(Request $request): TeacherProfile
    {
        // Users reaching this controller passed the teacher middleware, but
        // legacy role-based accounts (teacher/school/admin) may not have a
        // profile row yet — create their draft on first use.
        return $request->user()->teacherProfile
            ?? TeacherProfile::createDraftFor($request->user());
    }

    /** Minimum content required before a profile may enter the review queue. */
    private function missingRequiredFields(TeacherProfile $profile): array
    {
        $missing = [];

        if (blank($profile->headline)) {
            $missing[] = __('teacher.fields.headline');
        }
        if (blank($profile->expertise)) {
            $missing[] = __('teacher.fields.expertise');
        }
        if (blank($profile->about)) {
            $missing[] = __('teacher.fields.about');
        }
        if (blank($profile->primary_instrument)) {
            $missing[] = __('teacher.fields.primary_instrument');
        }
        if (! $profile->user->hasAvatar()) {
            $missing[] = __('teacher.fields.profile_photo');
        }

        return $missing;
    }
}
