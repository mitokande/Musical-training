<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCoachingSession;
use App\Models\Article;
use App\Models\DailyExerciseCount;
use App\Models\FeedItem;
use App\Models\GameScore;
use App\Models\Message;
use App\Models\User;
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

        // Engagement: daily / weekly / monthly active users
        $dau = User::whereDate('last_active_at', today())->count();
        $wau = User::where('last_active_at', '>=', now()->subDays(7))->count();
        $mau = User::where('last_active_at', '>=', now()->subDays(30))->count();
        $inactiveUsers = User::where(function ($q) {
            $q->where('last_active_at', '<', now()->subDays(30))
                ->orWhereNull('last_active_at');
        })->count();

        // Registrations
        $registrationsToday = User::whereDate('created_at', today())->count();
        $registrationsWeek = User::where('created_at', '>=', now()->subDays(7))->count();

        // Exercise stats
        $exerciseToday = DailyExerciseCount::whereDate('date', today())->sum('count');
        $exerciseWeek = DailyExerciseCount::where('date', '>=', now()->subDays(7))->sum('count');
        $exerciseMonth = DailyExerciseCount::where('date', '>=', now()->subDays(30))->sum('count');

        // AI, games, social (last 7 days)
        $aiSessionsWeek = AiCoachingSession::where('created_at', '>=', now()->subDays(7))->count();
        $aiTokensWeek = (int) AiCoachingSession::where('created_at', '>=', now()->subDays(7))->sum('tokens_used');
        $gamePlaysWeek = GameScore::where('created_at', '>=', now()->subDays(7))->count();
        $feedItemsWeek = FeedItem::where('created_at', '>=', now()->subDays(7))->count();

        // Pending articles and unread messages
        $pendingArticles = Article::pending()->count();
        $unreadMessages = Message::whereNull('read_at')
            ->where('receiver_id', auth()->id())
            ->count();

        $userCount = $userCounts->sum();
        $premiumCount = $planCounts->get('premium', 0);

        $stats = [
            'user_count' => $userCount,
            'student_count' => $userCounts->get('user', 0),
            'teacher_count' => $userCounts->get('teacher', 0),
            'school_count' => $userCounts->get('school', 0),
            'admin_count' => $userCounts->get('admin', 0),
            'free_count' => $planCounts->get('free', 0),
            'premium_count' => $premiumCount,
            'premium_rate' => $userCount > 0 ? round($premiumCount / $userCount * 100, 1) : 0,
            'active_users' => $wau,
            'dau' => $dau,
            'wau' => $wau,
            'mau' => $mau,
            'inactive_users' => $inactiveUsers,
            'registrations_today' => $registrationsToday,
            'registrations_week' => $registrationsWeek,
            'exercise_today' => $exerciseToday,
            'exercise_week' => $exerciseWeek,
            'exercise_month' => $exerciseMonth,
            'ai_sessions_week' => $aiSessionsWeek,
            'ai_tokens_week' => $aiTokensWeek,
            'game_plays_week' => $gamePlaysWeek,
            'feed_items_week' => $feedItemsWeek,
            'pending_articles' => $pendingArticles,
            'unread_messages' => $unreadMessages,
        ];

        // Chart data: user registration trend (last 30 days) as [{date, count}]
        $registrationTrend = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => $r->date, 'count' => $r->count])
            ->values();

        // Chart data: plan distribution as [{label, count}]
        $planDistribution = $planCounts
            ->map(fn ($count, $plan) => ['label' => ucfirst($plan ?: 'unknown'), 'count' => $count])
            ->values();

        // Chart data: exercise volume (last 14 days) as [{date, total}]
        $exerciseVolume = DailyExerciseCount::select('date', DB::raw('SUM(count) as total'))
            ->where('date', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => (string) $r->date, 'total' => (int) $r->total])
            ->values();

        // Latest registered members
        $recentUsers = User::latest()->take(6)->get(['id', 'name', 'email', 'plan', 'role', 'created_at']);

        // Most active members (exercise volume, last 7 days)
        $topUsers = DailyExerciseCount::select('user_id', DB::raw('SUM(count) as total'))
            ->where('date', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user:id,name,email,plan')
            ->take(6)
            ->get()
            ->filter(fn ($r) => $r->user);

        // Recent activity log
        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'registrationTrend',
            'planDistribution',
            'exerciseVolume',
            'recentUsers',
            'topUsers',
            'recentActivities'
        ));
    }
}
