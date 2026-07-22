<?php

namespace App\Http\Controllers;

use App\Models\FeedItem;
use App\Models\LearningPathExercise;
use App\Models\User;
use App\Models\UserPractice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            // Legacy teacher accounts land in the new Teacher CRM; the old
            // dashboards/teacher view is superseded by teacher.dashboard.
            'teacher' => redirect()->route('teacher.dashboard'),
            // School accounts land in the school panel (shared CRM engine).
            'school' => redirect()->route('school.dashboard'),
            default => $this->userDashboard($user),
        };
    }

    /**
     * Teacher-facing social feed. Renders the exact same social dashboard page
     * students get (dashboards.user: feed + sidebar), only the hero "Welcome back"
     * box swaps to a teacher-specific one (handled in the blade via hasTeacherAccount()).
     */
    public function feed(Request $request)
    {
        return $this->userDashboard($request->user(), 'feed');
    }

    protected function userDashboard(User $user, string $navActive = 'dashboard')
    {
        // --- Practice stats for the hero stat cards ---
        $userPractices = UserPractice::where('user_id', $user->id)->get();
        $totalSessions = $userPractices->count();
        $totalQuestions = $userPractices->sum('total_questions');
        $totalCorrect = $userPractices->sum('correct_answers');
        $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100) : 0;
        $streak = FeedItem::currentStreakForUser($user->id);

        // --- Sidebar: popular exercises ---
        $popularExercises = LearningPathExercise::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        // --- Sidebar: people to follow (most-followed users I don't follow yet) ---
        // Eager-load teacherProfile so the blade can colour each avatar by
        // teacher/student + premium/free without N+1 queries.
        $excludeIds = $user->following()->pluck('users.id')->push($user->id)->all();
        $suggestedUsers = User::whereNotIn('id', $excludeIds)
            ->whereNull('suspended_at')
            ->with('teacherProfile')
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboards.user', [
            'user' => $user,
            'navActive' => $navActive,
            'totalSessions' => $totalSessions,
            'accuracy' => $accuracy,
            'streak' => $streak,
            'followersCount' => $user->followersCount(),
            'popularExercises' => $popularExercises,
            'suggestedUsers' => $suggestedUsers,
        ]);
    }
}
