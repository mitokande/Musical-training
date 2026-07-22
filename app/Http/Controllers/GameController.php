<?php

namespace App\Http\Controllers;

use App\Models\FeedItem;
use App\Models\GameScore;
use App\Services\UsageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public const GAMES = [
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
        'chord-clash' => [
            'name' => 'Chord Clash',
            'description' => 'Hear a chord and identify its quality. 5 levels from triads to seventh chords.',
            'icon' => 'layers',
            'color' => 'from-rose-400 to-pink-600',
            'badge_color' => 'bg-pink-100 text-pink-700',
            'difficulty' => 'Advanced',
            'duration' => '~5 min',
            'tags' => ['Chords', 'Harmony'],
        ],
    ];

    private function indexData(): array
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
            $totalLimit = $user->getPlanLimit('games_daily_plays_total');
            $canAccessLeaderboard = $user->getPlanLimit('games_leaderboard');
        }

        $globalLeaderboard = GameScore::globalLeaderboard(20);

        return compact('scores', 'perTypeLimit', 'totalLimit', 'canAccessLeaderboard', 'globalLeaderboard');
    }

    public function index()
    {
        return view('games.index', $this->indexData());
    }

    public function testA()
    {
        return view('games.index', [...$this->indexData(), 'variant' => 'a']);
    }

    public function testB()
    {
        return view('games.index', [...$this->indexData(), 'variant' => 'b']);
    }

    public function testC()
    {
        return view('games.test-c', $this->indexData());
    }

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();
        $game = self::GAMES[$slug];

        $personalBestRecord = null;
        $personalBest = 0;
        $perTypeLimit = null;
        $totalLimit = null;
        $canAccessLeaderboard = false;
        $dailyPlaysUsed = 0;
        $totalPlaysUsed = 0;
        $canPlay = true;
        $weeklyLeaderboard = collect();
        $allTimeLeaderboard = collect();
        $userWeeklyRank = null;

        if ($user) {
            $personalBestRecord = GameScore::personalBest($user->id, $slug);
            $personalBest = $personalBestRecord?->score ?? 0;
            $perTypeLimit = $user->getPlanLimit('games_daily_plays_per_type');
            $totalLimit = $user->getPlanLimit('games_daily_plays_total');
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
            // Guest: server-side per-game daily counter (renews every day).
            // Only Level 1 is playable; level 2+ prompts sign-up in the UI.
            $usage = app(UsageQuotaService::class);
            $dailyPlaysUsed = $usage->guestUsed(request(), UsageQuotaService::FEATURE_GAME_PREFIX.$slug);
            $totalPlaysUsed = $dailyPlaysUsed;
            $perTypeLimit = (int) config('plans.guest.games_daily_plays_per_type', 1);
            $totalLimit = -1; // no separate all-games cap; each game is capped per day
            $canPlay = $dailyPlaysUsed < $perTypeLimit;
        }

        $scoreUrl = $user
            ? route('games.score', $slug)
            : route('games.guest-track', $slug);

        $maxUnlockedLevel = 1;
        if ($user && $slug === 'melody-memory') {
            try {
                $unlocked = GameScore::where('user_id', $user->id)
                    ->where('game_slug', $slug)
                    ->where('metadata->level_unlock', 1)
                    ->max('level_reached');
                $maxUnlockedLevel = max(1, min(3, (int) ($unlocked ?? 1)));
            } catch (\Exception $e) {
                $maxUnlockedLevel = 1;
            }
        }

        // Guests may only play level 1; higher levels prompt sign-up.
        $guestMaxLevel = $user ? null : (int) config('plans.guest.games_max_level', 1);

        return view('games.show', compact(
            'slug', 'game', 'personalBest', 'personalBestRecord',
            'perTypeLimit', 'totalLimit', 'dailyPlaysUsed', 'totalPlaysUsed', 'canPlay',
            'canAccessLeaderboard', 'scoreUrl',
            'weeklyLeaderboard', 'allTimeLeaderboard', 'userWeeklyRank',
            'maxUnlockedLevel', 'guestMaxLevel'
        ));
    }

    public function trackGuestPlay(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        // Guest scores are never persisted — only the daily play counter is.
        $usage = app(UsageQuotaService::class);
        $feature = UsageQuotaService::FEATURE_GAME_PREFIX.$slug;
        $usage->guestIncrement($request, $feature);

        $perTypeLimit = (int) config('plans.guest.games_daily_plays_per_type', 1);
        $canPlayAgain = $usage->guestUsed($request, $feature) < $perTypeLimit;

        return response()->json([
            'success' => true,
            'can_play_again' => $canPlayAgain,
        ]);
    }

    public function storeScore(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::GAMES), 404);

        $user = Auth::user();

        $perTypeLimit = $user->getPlanLimit('games_daily_plays_per_type');
        $totalLimit = $user->getPlanLimit('games_daily_plays_total');

        // Server-side per-type limit enforcement
        if ($perTypeLimit !== -1) {
            $playsToday = GameScore::where('user_id', $user->id)
                ->where('game_slug', $slug)
                ->whereDate('created_at', today())
                ->count();

            if ($playsToday >= $perTypeLimit) {
                return response()->json([
                    'success' => false,
                    'limit_reached' => true,
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
                    'success' => false,
                    'limit_reached' => true,
                    'can_play_again' => false,
                ]);
            }
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0',
            'max_streak' => 'required|integer|min:0',
            'level_reached' => 'required|integer|min:1',
            'metadata' => 'nullable|array',
        ]);

        $gameScore = GameScore::create([
            'user_id' => $user->id,
            'game_slug' => $slug,
            'score' => $validated['score'],
            'max_streak' => $validated['max_streak'],
            'level_reached' => $validated['level_reached'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $personalBest = GameScore::personalBest($user->id, $slug);
        $isNewBest = $personalBest->id === $gameScore->id;

        // Share a "new personal best" achievement to the social feed.
        if ($isNewBest && $gameScore->score > 0) {
            FeedItem::recordGameHighScore($user, $slug, self::GAMES[$slug]['name'], $gameScore->score);
        }

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
            'success' => true,
            'is_new_best' => $isNewBest,
            'personal_best' => $personalBest->score,
            'can_play_again' => $canPlayAgain,
        ]);
    }
}
