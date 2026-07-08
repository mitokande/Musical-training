<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AiUsageLogExport;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from')->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $request->filled('to') ? $request->date('to')->endOfDay() : now()->endOfDay();

        $baseQuery = function () use ($request, $from, $to) {
            return AiUsageLog::query()
                ->when($request->filled('feature'), fn ($q) => $q->where('feature', $request->feature))
                ->when($request->filled('model'), fn ($q) => $q->where('model', $request->model))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->whereBetween('created_at', [$from, $to]);
        };

        $totalRequests = $baseQuery()->count();
        $totalTokens = (int) $baseQuery()->sum('total_tokens');
        $totalCost = (float) $baseQuery()->sum('cost_usd');
        $activeUsers = $baseQuery()->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $errorCount = $baseQuery()->where('status', 'error')->count();
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 1) : 0;

        $usageTrend = $baseQuery()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as requests'),
                DB::raw('SUM(total_tokens) as tokens'),
                DB::raw('SUM(cost_usd) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byFeature = $baseQuery()
            ->select('feature', DB::raw('count(*) as requests'), DB::raw('SUM(total_tokens) as tokens'), DB::raw('SUM(cost_usd) as cost'))
            ->groupBy('feature')
            ->orderByDesc('requests')
            ->get();

        $byModel = $baseQuery()
            ->select('model', DB::raw('count(*) as requests'), DB::raw('SUM(total_tokens) as tokens'), DB::raw('SUM(cost_usd) as cost'))
            ->groupBy('model')
            ->orderByDesc('requests')
            ->get();

        $topUsers = $baseQuery()
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('count(*) as requests'), DB::raw('SUM(total_tokens) as tokens'), DB::raw('SUM(cost_usd) as cost'))
            ->groupBy('user_id')
            ->orderByDesc('cost')
            ->with('user:id,name,email')
            ->take(10)
            ->get();

        $logs = $baseQuery()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $features = AiUsageLog::distinct()->orderBy('feature')->pluck('feature');
        $models = AiUsageLog::distinct()->orderBy('model')->pluck('model');

        return view('admin.ai-usage.index', compact(
            'totalRequests', 'totalTokens', 'totalCost', 'activeUsers', 'errorRate',
            'usageTrend', 'byFeature', 'byModel', 'topUsers', 'logs', 'features', 'models',
            'from', 'to'
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(new AiUsageLogExport($request), 'ai-usage-log.xlsx');
    }
}
