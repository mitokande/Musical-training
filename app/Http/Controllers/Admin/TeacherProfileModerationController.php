<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Services\Teacher\TeacherProfileModerationService;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherProfileModerationController extends Controller
{
    public function __construct(
        private TeacherProfileModerationService $moderation,
        private TeacherSubscriptionBenefitService $benefits,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $profiles = TeacherProfile::with('user')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = TeacherProfile::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.teacher-profiles.index', [
            'profiles' => $profiles,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    public function show(TeacherProfile $teacherProfile): View
    {
        $teacherProfile->load([
            'user', 'educations', 'instruments', 'services', 'videos', 'media',
            'paymentLinks', 'moderationLogs.admin',
        ]);

        return view('admin.teacher-profiles.show', [
            'profile' => $teacherProfile,
            'activeBenefit' => $this->benefits->activeBenefit($teacherProfile->user),
            'eligibleStudentCount' => $this->benefits->eligibleStudentCount($teacherProfile->user),
        ]);
    }

    public function approve(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $this->moderation->approve($teacherProfile, $request->user(), $request->input('notes'));

        return back()->with('status', 'profile-approved');
    }

    public function reject(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        $this->moderation->reject($teacherProfile, $request->user(), $request->input('reason'));

        return back()->with('status', 'profile-rejected');
    }

    public function suspend(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $this->moderation->suspend($teacherProfile, $request->user(), $request->input('reason'));

        return back()->with('status', 'profile-suspended');
    }

    public function reinstate(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $this->moderation->reinstate($teacherProfile, $request->user(), $request->input('notes'));

        return back()->with('status', 'profile-reinstated');
    }

    public function forcePrivate(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $this->moderation->forcePrivate(
            $teacherProfile,
            $request->user(),
            $request->boolean('private'),
            $request->input('notes'),
        );

        return back()->with('status', 'profile-visibility-updated');
    }

    public function addNote(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $request->validate(['notes' => ['required', 'string', 'max:2000']]);

        $this->moderation->addNote($teacherProfile, $request->user(), $request->input('notes'));

        return back()->with('status', 'note-added');
    }

    /** Manual tier assignment — the only way to grant Teacher Premium in Phase 1. */
    public function updateTier(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $request->validate(['tier' => ['required', 'in:basic,premium']]);

        $from = $teacherProfile->tier;
        $teacherProfile->update(['tier' => $request->input('tier')]);

        activity('teacher')
            ->causedBy($request->user())
            ->performedOn($teacherProfile)
            ->withProperties(['from' => $from, 'to' => $teacherProfile->tier])
            ->log('teacher_tier_changed');

        return back()->with('status', 'tier-updated');
    }

    public function recalculateBenefits(Request $request, TeacherProfile $teacherProfile): RedirectResponse
    {
        $this->authorize('moderate', $teacherProfile);
        $this->benefits->recalculate($teacherProfile->user);

        return back()->with('status', 'benefits-recalculated');
    }
}
