<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCoachingSession;
use App\Models\Article;
use App\Models\DailyExerciseCount;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserPractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function members()
    {
        $registrationTrend = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role');

        $planBreakdown = User::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $activeCount = User::where('last_active_at', '>=', now()->subDays(7))->count();
        $inactiveCount = User::where(function ($q) {
            $q->where('last_active_at', '<', now()->subDays(30))->orWhereNull('last_active_at');
        })->count();

        return view('admin.reports.members', compact(
            'registrationTrend', 'roleDistribution', 'planBreakdown', 'activeCount', 'inactiveCount'
        ));
    }

    public function revenue()
    {
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->select(DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        return view('admin.reports.revenue', compact('monthlyRevenue', 'totalRevenue', 'revenueThisMonth'));
    }

    public function subscriptions()
    {
        $activeCount = Subscription::where('status', 'active')->count();
        $cancelledCount = Subscription::where('status', 'cancelled')->count();
        $totalCount = Subscription::count();

        $conversionRate = $totalCount > 0
            ? round(($activeCount / $totalCount) * 100, 1)
            : 0;

        $byPlan = Subscription::select('plan_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('plan_id', 'status')
            ->with('plan:id,name')
            ->get();

        return view('admin.reports.subscriptions', compact(
            'activeCount', 'cancelledCount', 'totalCount', 'conversionRate', 'byPlan'
        ));
    }

    public function exercises()
    {
        $mostPracticed = UserPractice::select('practice_id', DB::raw('SUM(total_questions) as total'))
            ->groupBy('practice_id')
            ->orderByDesc('total')
            ->with('practice:id,name')
            ->take(10)
            ->get();

        $averageScores = UserPractice::select(
                'practice_id',
                DB::raw('AVG(correct_answers / NULLIF(total_questions, 0) * 100) as avg_score')
            )
            ->groupBy('practice_id')
            ->with('practice:id,name')
            ->get();

        $dailyVolume = DailyExerciseCount::select('date', DB::raw('SUM(count) as total'))
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return view('admin.reports.exercises', compact('mostPracticed', 'averageScores', 'dailyVolume'));
    }

    public function aiCoach()
    {
        $sessionCount = AiCoachingSession::count();

        $tokenUsageTrend = AiCoachingSession::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(tokens_used) as tokens'),
                DB::raw('count(*) as sessions')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.ai-coach', compact('sessionCount', 'tokenUsageTrend'));
    }

    public function content()
    {
        $byType = Article::select('content_type', DB::raw('count(*) as count'))
            ->groupBy('content_type')
            ->pluck('count', 'content_type');

        $byStatus = Article::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalViews = Article::sum('view_count');

        $topArticles = Article::orderByDesc('view_count')->take(10)->get(['id', 'title', 'view_count', 'content_type']);

        return view('admin.reports.content', compact('byType', 'byStatus', 'totalViews', 'topArticles'));
    }

    public function export($type)
    {
        $exportClass = match ($type) {
            'members'       => \App\Exports\UsersExport::class,
            'revenue'       => \App\Exports\RevenueExport::class,
            'subscriptions' => \App\Exports\SubscriptionsExport::class,
            'exercises'     => \App\Exports\ExercisesExport::class,
            default         => abort(404),
        };

        return Excel::download(new $exportClass(request()), "{$type}-report.xlsx");
    }
}
