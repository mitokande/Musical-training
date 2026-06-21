<?php

namespace App\Http\Controllers;

use App\Models\GameScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    const GUEST_PLAYS_PER_TYPE = 1;
    const GUEST_PLAYS_TOTAL    = 3;

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
        $perTypeLimit = null;
        $totalLimit = null;
        $canAccessLeaderboard = false;

        if ($user) {
            foreach (array_keys(self::GAMES) as $slug) {
                $scores[$slug] = GameScore::personalBest($user->id, $slug);
            }
            $perTypeLimit = $user->getPlanLimit('games_daily_plays_per_type');
            $totalLimit   = $user->getPlanLimit('games_daily_plays_total');
            $canAccessLeaderboard = $user->getPlanLimit('games_leaderboard');
        }

        $globalLeaderboard = GameScore::globalLeaderboard(20);

        return view('games.index', compact('scores', 'perTypeLimit', 'totalLimit', 'canAccessLeaderboard', 'globalLeaderboard'));
    }

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();
        $game = self::GAMES[$slug];

        $personalBestRecord   = null;
        $personalBest         = 0;
        $perTypeLimit         = null;
        $totalLimit           = null;
        $canAccessLeaderboard = false;
        $dailyPlaysUsed       = 0;
        $totalPlaysUsed       = 0;
        $canPlay              = true;
        $weeklyLeaderboard    = collect();
        $allTimeLeaderboard   = collect();
        $userWeeklyRank       = null;

        if ($user) {
            $personalBestRecord = GameScore::personalBest($user->id, $slug);
            $personalBest       = $personalBestRecord?->score ?? 0;
            $perTypeLimit       = $user->getPlanLimit('games_daily_plays_per_type');
            $totalLimit         = $user->getPlanLimit('games_daily_plays_total');
            $canAccessLeaderboard = $user->getPlanLimit('games_leaderboard');

            if ($perTypeLimit !== -1) {
                $dailyPlaysUsed = GameScore::where('user_id', $user->id)
                    ->where('game_slug', $slug)
                    ->whereDate('created_at', today())
                    ->count();
                $canPlay = $dailyPlaysUsed < $perTypeLimit;
            }

            if ($totalLimit !== -1) {
                $totalPlaysUsed = GameScore::where('user_id', $user->id)
                    ->whereDate('created_at', today())
                    ->count();
                if ($canPlay) {
                    $canPlay = $totalPlaysUsed < $totalLimit;
                }
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
        } else {
            // Guest: session-based tracking (one-time, not daily)
            $guestPlays     = session('guest_game_plays', []);
            $dailyPlaysUsed = $guestPlays[$slug] ?? 0;
            $totalPlaysUsed = array_sum($guestPlays);
            $perTypeLimit   = self::GUEST_PLAYS_PER_TYPE;
            $totalLimit     = self::GUEST_PLAYS_TOTAL;
            $canPlay        = $dailyPlaysUsed < $perTypeLimit && $totalPlaysUsed < $totalLimit;
        }

        $scoreUrl = $user
            ? route('games.score', $slug)
            : route('games.guest-track', $slug);

        return view('games.show', compact(
            'slug', 'game', 'personalBest', 'personalBestRecord',
            'perTypeLimit', 'totalLimit', 'dailyPlaysUsed', 'totalPlaysUsed', 'canPlay',
            'canAccessLeaderboard', 'scoreUrl',
            'weeklyLeaderboard', 'allTimeLeaderboard', 'userWeeklyRank'
        ));
    }

    public function trackGuestPlay(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $guestPlays          = session('guest_game_plays', []);
        $guestPlays[$slug]   = ($guestPlays[$slug] ?? 0) + 1;
        session(['guest_game_plays' => $guestPlays]);

        $playsThisType = $guestPlays[$slug];
        $totalPlays    = array_sum($guestPlays);
        $canPlayAgain  = $playsThisType < self::GUEST_PLAYS_PER_TYPE
                         && $totalPlays < self::GUEST_PLAYS_TOTAL;

        return response()->json([
            'success'        => true,
            'can_play_again' => $canPlayAgain,
        ]);
    }

    public function storeScore(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();

        $perTypeLimit = $user->getPlanLimit('games_daily_plays_per_type');
        $totalLimit   = $user->getPlanLimit('games_daily_plays_total');

        // Server-side per-type limit enforcement
        if ($perTypeLimit !== -1) {
            $playsToday = GameScore::where('user_id', $user->id)
                ->where('game_slug', $slug)
                ->whereDate('created_at', today())
                ->count();

            if ($playsToday >= $perTypeLimit) {
                return response()->json([
                    'success'        => false,
                    'limit_reached'  => true,
                    'can_play_again' => false,
                ]);
            }
        }

        // Server-side total limit enforcement
        if ($totalLimit !== -1) {
            $totalToday = GameScore::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();

            if ($totalToday >= $totalLimit) {
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

        // Determine if the user can play again (check both limits after saving)
        $canPlayAgain = true;

        if ($perTypeLimit !== -1) {
            $playsNow = GameScore::where('user_id', $user->id)
                ->where('game_slug', $slug)
                ->whereDate('created_at', today())
                ->count();
            if ($playsNow >= $perTypeLimit) {
                $canPlayAgain = false;
            }
        }

        if ($canPlayAgain && $totalLimit !== -1) {
            $totalNow = GameScore::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->count();
            if ($totalNow >= $totalLimit) {
                $canPlayAgain = false;
            }
        }

        return response()->json([
            'success'        => true,
            'is_new_best'    => $isNewBest,
            'personal_best'  => $personalBest->score,
            'can_play_again' => $canPlayAgain,
        ]);
    }
}
