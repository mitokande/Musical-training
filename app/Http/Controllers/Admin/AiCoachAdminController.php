<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiCoachingSession;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiCoachAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sessions' => AiCoachingSession::count(),
            'total_tokens'   => AiCoachingSession::sum('tokens_used'),
            'unique_users'   => AiCoachingSession::distinct('user_id')->count('user_id'),
        ];

        $sessionsPerUser = AiCoachingSession::select('user_id', DB::raw('count(*) as session_count'))
            ->groupBy('user_id')
            ->orderByDesc('session_count')
            ->with('user:id,name,email')
            ->take(20)
            ->get();

        $sessionsTrend = AiCoachingSession::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('SUM(tokens_used) as tokens')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.ai-coach.index', compact('stats', 'sessionsPerUser', 'sessionsTrend'));
    }

    public function userProfile(User $user)
    {
        $user->load(['questionnaireResponses', 'userPractices.practice', 'aiCoachingSessions']);

        $practiceStats = $user->userPractices()
            ->selectRaw('SUM(total_questions) as total_questions, SUM(correct_answers) as correct, AVG(average_time) as avg_time')
            ->first();

        return view('admin.ai-coach.user-profile', compact('user', 'practiceStats'));
    }

    public function settings()
    {
        $settings = [
            'ai_model'             => SystemSetting::get('ai_model', 'gpt-4'),
            'ai_max_tokens'        => SystemSetting::get('ai_max_tokens', 1000),
            'ai_temperature'       => SystemSetting::get('ai_temperature', 0.7),
            'ai_daily_limit'       => SystemSetting::get('ai_daily_limit', 10),
            'ai_premium_limit'     => SystemSetting::get('ai_premium_limit', 50),
            'ai_system_prompt'     => SystemSetting::get('ai_system_prompt', ''),
            'ai_enabled'           => SystemSetting::get('ai_enabled', true),
        ];

        return view('admin.ai-coach.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'ai_model'         => 'required|string',
            'ai_max_tokens'    => 'required|integer|min:100|max:8000',
            'ai_temperature'   => 'required|numeric|min:0|max:2',
            'ai_daily_limit'   => 'required|integer|min:1',
            'ai_premium_limit' => 'required|integer|min:1',
            'ai_system_prompt' => 'nullable|string',
            'ai_enabled'       => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, is_bool($value) ? 'boolean' : 'string', 'ai_coach');
        }

        return back()->with('success', 'AI Coach settings updated successfully.');
    }
}
