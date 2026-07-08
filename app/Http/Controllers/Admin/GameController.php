<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameScore;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $settings = [
            'game_enabled' => SystemSetting::get('game_enabled', true),
            'game_difficulty_levels' => SystemSetting::get('game_difficulty_levels', 'easy,medium,hard'),
            'game_time_limit' => SystemSetting::get('game_time_limit', 60),
            'game_points_correct' => SystemSetting::get('game_points_correct', 10),
            'game_points_streak' => SystemSetting::get('game_points_streak', 5),
            'game_leaderboard_size' => SystemSetting::get('game_leaderboard_size', 100),
        ];

        $games = collect(\App\Http\Controllers\GameController::GAMES)
            ->map(fn ($game) => $game['name']);

        $gameStats = GameScore::select(
            'game_slug',
            DB::raw('count(*) as plays'),
            DB::raw('count(distinct user_id) as players'),
            DB::raw('max(score) as top_score')
        )
            ->groupBy('game_slug')
            ->get()
            ->keyBy('game_slug');

        $scores = GameScore::with('user:id,name,email')
            ->when($request->game, fn ($q, $slug) => $q->where('game_slug', $slug))
            ->orderByDesc('score')
            ->paginate(20)
            ->withQueryString();

        return view('admin.games.index', compact('settings', 'games', 'gameStats', 'scores'));
    }

    public function destroyScore(GameScore $score)
    {
        $score->delete();

        return back()->with('success', 'Game score deleted.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'game_enabled' => 'boolean',
            'game_difficulty_levels' => 'required|string',
            'game_time_limit' => 'required|integer|min:10|max:300',
            'game_points_correct' => 'required|integer|min:1',
            'game_points_streak' => 'required|integer|min:1',
            'game_leaderboard_size' => 'required|integer|min:10|max:500',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, is_bool($value) ? 'boolean' : 'string', 'games');
        }

        return back()->with('success', 'Game settings updated successfully.');
    }
}
