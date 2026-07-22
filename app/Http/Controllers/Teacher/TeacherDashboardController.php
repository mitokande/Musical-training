<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolTeacherRelationship;
use App\Models\TeacherAppointment;
use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherConversationMessage;
use App\Models\TeacherProfile;
use App\Models\TeacherStudentRelationship;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherSubscriptionBenefitService $benefits,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->teacherProfile ?? new TeacherProfile(['tier' => TeacherProfile::TIER_BASIC, 'status' => TeacherProfile::STATUS_DRAFT]);

        // Schools count their member teachers' active students too.
        $activeStudentCount = TeacherStudentRelationship::active()
            ->whereIn('teacher_id', $user->crmOwnerIds())
            ->count();

        $pendingStudentCount = TeacherStudentRelationship::query()
            ->whereIn('teacher_id', $user->crmOwnerIds())
            ->where('status', TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL)
            ->count();

        $newStudentsThisMonth = TeacherStudentRelationship::active()
            ->whereIn('teacher_id', $user->crmOwnerIds())
            ->where('approved_at', '>=', now()->startOfMonth())
            ->count();

        $teacherStats = null;
        if ($profile->exists && $profile->isSchoolEntity()) {
            $memberIds = $user->memberTeacherIds();

            $memberAssignmentBase = TeacherAssignmentRecipient::whereHas(
                'assignment', fn ($q) => $q->whereIn('teacher_id', $memberIds)
            );

            $teacherStats = [
                'active_teachers' => SchoolTeacherRelationship::active()
                    ->where('school_id', $user->id)->count(),
                'pending_teachers' => SchoolTeacherRelationship::where('school_id', $user->id)
                    ->where('status', SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL)
                    ->count(),
                'member_students' => TeacherStudentRelationship::active()
                    ->whereIn('teacher_id', $memberIds)
                    ->distinct('student_id')
                    ->count('student_id'),
                'member_classes' => TeacherClass::whereIn('teacher_id', $memberIds)
                    ->whereNull('archived_at')
                    ->count(),
                'member_assignments' => TeacherAssignment::whereIn('teacher_id', $memberIds)
                    ->count(),
                'average_score' => $memberIds === []
                    ? null
                    : $memberAssignmentBase->clone()->whereNotNull('best_score')->avg('best_score'),
            ];
        }

        $assignmentBase = TeacherAssignmentRecipient::whereHas(
            'assignment', fn ($q) => $q->where('teacher_id', $user->id)
        );

        return view('teacher.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'capabilities' => $this->capabilities->capabilities($user),
            'activeBenefit' => $profile->exists ? $this->benefits->activeBenefit($user) : null,
            'eligibleStudentCount' => $profile->exists ? $this->benefits->eligibleStudentCount($user) : 0,
            'benefitSettings' => [
                'enabled' => $this->benefits->isEnabled(),
                'discount_threshold' => $this->benefits->discountThreshold(),
                'discount_percentage' => $this->benefits->discountPercentage(),
                'free_threshold' => $this->benefits->freePeriodThreshold(),
                'free_months' => $this->benefits->freePeriodMonths(),
            ],
            'notifications' => $user->notifications()->latest()->limit(10)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
            'teacherStats' => $teacherStats,
            // Row 2 widgets: appointment requests, upcoming lessons, student stats.
            'pendingAppointments' => TeacherAppointment::with('student')
                ->where('teacher_id', $user->id)
                ->whereIn('status', [
                    TeacherAppointment::STATUS_PENDING,
                    TeacherAppointment::STATUS_RESCHEDULE_REQUESTED,
                ])
                ->orderBy('starts_at')->limit(5)->get(),
            'upcomingLessons' => TeacherAppointment::with('student')
                ->where('teacher_id', $user->id)
                ->where('status', TeacherAppointment::STATUS_CONFIRMED)
                ->upcoming()->orderBy('starts_at')->limit(5)->get(),
            'studentStats' => [
                'active_students' => $activeStudentCount,
                'pending_students' => $pendingStudentCount,
                'new_students_month' => $newStudentsThisMonth,
                'classes' => $user->teacherClasses()->whereNull('archived_at')->count(),
                'assignments_open' => $assignmentBase->clone()
                    ->where('status', '!=', TeacherAssignmentRecipient::STATUS_COMPLETED)->count(),
                'assignments_completed' => $assignmentBase->clone()
                    ->where('status', TeacherAssignmentRecipient::STATUS_COMPLETED)->count(),
                'average_score' => $assignmentBase->clone()->whereNotNull('best_score')->avg('best_score'),
                'unread_messages' => TeacherConversationMessage::whereNull('read_at')
                    ->where('sender_id', '!=', $user->id)
                    ->whereHas('conversation', fn ($q) => $q->where('teacher_id', $user->id))
                    ->count(),
            ],
        ]);
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /** Account settings inside the CRM: photo, language, password. */
    public function settings(Request $request): View
    {
        return view('teacher.settings', [
            'user' => $request->user(),
            'capabilities' => $this->capabilities->capabilities($request->user()),
        ]);
    }

    /** Inline edit of the account card on the CRM settings page. */
    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            // Dots allowed: legacy usernames (and faker data) contain them.
            'username' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+$/', 'min:3', 'max:50',
                Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'surname' => $validated['surname'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $emailChanged = $user->isDirty('email');
        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route(crm_prefix().'.settings')->with('status', 'account-updated');
    }
}
