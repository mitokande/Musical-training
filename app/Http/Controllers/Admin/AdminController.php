<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\DailyExerciseCount;
use App\Models\Message;
use App\Models\User;
use App\Models\UserPractice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AdminController extends Controller
{
    public function dashboard()
    {
        // User counts by role
        $userCounts = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        // User counts by plan
        $planCounts = User::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        // Active users (last 7 days) and inactive (30+ days)
        $activeUsers = User::where('last_active_at', '>=', now()->subDays(7))->count();
        $inactiveUsers = User::where(function ($q) {
            $q->where('last_active_at', '<', now()->subDays(30))
              ->orWhereNull('last_active_at');
        })->count();

        // Exercise stats
        $exerciseToday = DailyExerciseCount::whereDate('date', today())->sum('count');
        $exerciseWeek = DailyExerciseCount::where('date', '>=', now()->subDays(7))->sum('count');
        $exerciseMonth = DailyExerciseCount::where('date', '>=', now()->subDays(30))->sum('count');

        // Pending articles and unread messages
        $pendingArticles = Article::pending()->count();
        $unreadMessages = Message::whereNull('read_at')
            ->where('receiver_id', auth()->id())
            ->count();

        $stats = [
            'user_count'       => $userCounts->sum(),
            'student_count'    => $userCounts->get('user', 0),
            'teacher_count'    => $userCounts->get('teacher', 0),
            'school_count'     => $userCounts->get('school', 0),
            'admin_count'      => $userCounts->get('admin', 0),
            'free_count'       => $planCounts->get('free', 0),
            'premium_count'    => $planCounts->get('premium', 0),
            'active_users'     => $activeUsers,
            'inactive_users'   => $inactiveUsers,
            'exercise_today'   => $exerciseToday,
            'exercise_week'    => $exerciseWeek,
            'exercise_month'   => $exerciseMonth,
            'pending_articles' => $pendingArticles,
            'unread_messages'  => $unreadMessages,
        ];

        // Chart data: user registration trend (last 30 days)
        $registrationTrend = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Chart data: plan distribution
        $planDistribution = $planCounts;

        // Chart data: exercise volume (last 7 days)
        $exerciseVolume = DailyExerciseCount::select(DB::raw('date'), DB::raw('SUM(count) as total'))
            ->where('date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Recent activity log
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'registrationTrend',
            'planDistribution',
            'exerciseVolume',
            'recentActivity'
        ));
    }
}
