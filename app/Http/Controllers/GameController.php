<?php

namespace App\Http\Controllers;

use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public const GAMES = [
        'note-rush' => [
            'name' => 'Note Rush',
            'description' => 'Identify notes as fast as you can before time runs out.',
            'icon' => 'zap',
            'color' => 'from-yellow-400 to-orange-500',
            'badge_color' => 'bg-orange-100 text-orange-700',
            'difficulty' => 'Beginner',
            'duration' => '60s',
            'tags' => ['Notes', 'Speed'],
        ],
        'melody-memory' => [
            'name' => 'Melody Memory',
            'description' => 'Listen to a melody and repeat it on the piano. How far can you go?',
            'icon' => 'music',
            'color' => 'from-purple-500 to-indigo-600',
            'badge_color' => 'bg-purple-100 text-purple-700',
            'difficulty' => 'Intermediate',
            'duration' => 'Endless',
            'tags' => ['Melody', 'Memory'],
        ],
        'interval-blitz' => [
            'name' => 'Interval Blitz',
            'description' => 'Name that interval before the timer runs out. 3 lives — use them wisely.',
            'icon' => 'timer',
            'color' => 'from-sky-400 to-blue-600',
            'badge_color' => 'bg-blue-100 text-blue-700',
            'difficulty' => 'Intermediate',
            'duration' => '~3 min',
            'tags' => ['Intervals', 'Speed'],
        ],
        'chord-clash' => [
            'name' => 'Chord Clash',
            'description' => 'Two chords play — pick the right one. How many rounds can you clear?',
            'icon' => 'layers',
            'color' => 'from-rose-400 to-pink-600',
            'badge_color' => 'bg-pink-100 text-pink-700',
            'difficulty' => 'Advanced',
            'duration' => '~5 min',
            'tags' => ['Chords', 'Harmony'],
        ],
        'note-fall' => [
            'name' => 'Note Fall',
            'description' => 'Notes drop from above — press the matching piano key before they hit the bottom!',
            'icon' => 'arrow-down-to-line',
            'color' => 'from-emerald-400 to-teal-600',
            'badge_color' => 'bg-emerald-100 text-emerald-700',
            'difficulty' => 'Beginner',
            'duration' => 'Endless',
            'tags' => ['Notes', 'Reflex'],
        ],
        'note-catcher' => [
            'name' => 'Note Catcher',
            'description' => 'Steer the falling note left and right to land on the correct piano key.',
            'icon' => 'move-horizontal',
            'color' => 'from-violet-400 to-purple-600',
            'badge_color' => 'bg-violet-100 text-violet-700',
            'difficulty' => 'Beginner',
            'duration' => 'Endless',
            'tags' => ['Notes', 'Steering'],
        ],
    ];

    public function index()
    {
        $user = Auth::user();
        $scores = [];
        $dailyLimit = null;
        $canAccessLeaderboard = false;

        if ($user) {
            foreach (array_keys(self::GAMES) as $slug) {
                $scores[$slug] = GameScore::personalBest($user->id, $slug);
            }
            $dailyLimit = $user->getPlanLimit('games_daily_plays');
            $canAccessLeaderboard = $user->getPlanLimit('games_leaderboard');
        }

        $globalLeaderboard = GameScore::globalLeaderboard(20);

        return view('games.index', compact('scores', 'dailyLimit', 'canAccessLeaderboard', 'globalLeaderboard'));
    }

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();
        $game = self::GAMES[$slug];

        $personalBestRecord = null;
        $personalBest = 0;
        $dailyLimit = null;
        $canAccessLeaderboard = false;
        $dailyPlaysUsed = 0;
        $canPlay = true;
        $weeklyLeaderboard = collect();
        $allTimeLeaderboard = collect();
        $userWeeklyRank = null;

        if ($user) {
            $personalBestRecord = GameScore::personalBest($user->id, $slug);
            $personalBest = $personalBestRecord?->score ?? 0;
            $dailyLimit = $user->getPlanLimit('games_daily_plays');
            $canAccessLeaderboard = $user->getPlanLimit('games_leaderboard');

            if ($dailyLimit !== null && $dailyLimit !== -1) {
                $dailyPlaysUsed = GameScore::where('user_id', $user->id)
                    ->where('game_slug', $slug)
                    ->whereDate('created_at', today())
                    ->count();
                $canPlay = $dailyPlaysUsed < $dailyLimit;
            }

            $weeklyLeaderboard = $canAccessLeaderboard
                ? GameScore::weeklyLeaderboard($slug)
                : collect();

            $allTimeLeaderboard = $canAccessLeaderboard
                ? GameScore::allTimeLeaderboard($slug, 30)
                : collect();

            $userWeeklyRank = $canAccessLeaderboard
                ? GameScore::userRank($user->id, $slug, weekly: true)
                : null;
        }

        return view('games.show', compact(
            'slug', 'game', 'personalBest', 'personalBestRecord',
            'dailyLimit', 'dailyPlaysUsed', 'canPlay',
            'canAccessLeaderboard',
            'weeklyLeaderboard', 'allTimeLeaderboard', 'userWeeklyRank'
        ));
    }

    public function storeScore(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();

        // Server-side daily limit enforcement
        $dailyLimit = $user->getPlanLimit('games_daily_plays');
        if ($dailyLimit !== null && $dailyLimit !== -1) {
            $playsToday = GameScore::where('user_id', $user->id)
                ->where('game_slug', $slug)
                ->whereDate('created_at', today())
                ->count();

            if ($playsToday >= $dailyLimit) {
                return response()->json([
                    'success'        => false,
                    'limit_reached'  => true,
                    'can_play_again' => false,
                ]);
            }
        }

        $validated = $request->validate([
            'score'         => 'required|integer|min:0',
            'max_streak'    => 'required|integer|min:0',
            'level_reached' => 'required|integer|min:1',
            'metadata'      => 'nullable|array',
        ]);

        $gameScore = GameScore::create([
            'user_id'       => $user->id,
            'game_slug'     => $slug,
            'score'         => $validated['score'],
            'max_streak'    => $validated['max_streak'],
            'level_reached' => $validated['level_reached'],
            'metadata'      => $validated['metadata'] ?? null,
        ]);

        $personalBest = GameScore::personalBest($user->id, $slug);
        $isNewBest    = $personalBest->id === $gameScore->id;

        // Calculate remaining plays after this save
        $canPlayAgain = true;
        if ($dailyLimit !== null && $dailyLimit !== -1) {
            $playsNow = GameScore::where('user_id', $user->id)
                ->where('game_slug', $slug)
                ->whereDate('created_at', today())
                ->count();
            $canPlayAgain = $playsNow < $dailyLimit;
        }

        return response()->json([
            'success'        => true,
            'is_new_best'    => $isNewBest,
            'personal_best'  => $personalBest->score,
            'can_play_again' => $canPlayAgain,
        ]);
    }
}
