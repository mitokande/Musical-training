<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Http\Request;
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

        $activeStudentCount = $user->studentRelationships()->active()->count();

        $assignmentBase = \App\Models\TeacherAssignmentRecipient::whereHas(
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
            // Row 2 widgets: appointment requests, upcoming lessons, student stats.
            'pendingAppointments' => \App\Models\TeacherAppointment::with('student')
                ->where('teacher_id', $user->id)
                ->whereIn('status', [
                    \App\Models\TeacherAppointment::STATUS_PENDING,
                    \App\Models\TeacherAppointment::STATUS_RESCHEDULE_REQUESTED,
                ])
                ->orderBy('starts_at')->limit(5)->get(),
            'upcomingLessons' => \App\Models\TeacherAppointment::with('student')
                ->where('teacher_id', $user->id)
                ->where('status', \App\Models\TeacherAppointment::STATUS_CONFIRMED)
                ->upcoming()->orderBy('starts_at')->limit(5)->get(),
            'studentStats' => [
                'active_students' => $activeStudentCount,
                'classes' => $user->teacherClasses()->whereNull('archived_at')->count(),
                'assignments_open' => $assignmentBase->clone()
                    ->where('status', '!=', \App\Models\TeacherAssignmentRecipient::STATUS_COMPLETED)->count(),
                'assignments_completed' => $assignmentBase->clone()
                    ->where('status', \App\Models\TeacherAssignmentRecipient::STATUS_COMPLETED)->count(),
                'average_score' => $assignmentBase->clone()->whereNotNull('best_score')->avg('best_score'),
                'unread_messages' => \App\Models\TeacherConversationMessage::whereNull('read_at')
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
}
