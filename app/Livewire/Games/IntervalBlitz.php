<?php

namespace App\Livewire\Games;

use App\Models\GameScore;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IntervalBlitz extends Component
{
    public int $personalBest = 0;
    public int $dailyPlaysUsed = 0;
    public int $dailyLimit = 5;
    public bool $canPlay = true;

    public function mount(): void
    {
        $user = Auth::user();
        $this->dailyLimit = $user->getPlanLimit('games_daily_plays') ?? -1;

        $best = GameScore::personalBest($user->id, 'interval-blitz');
        $this->personalBest = $best?->score ?? 0;

        if ($this->dailyLimit !== -1) {
            $this->dailyPlaysUsed = GameScore::where('user_id', $user->id)
                ->where('game_slug', 'interval-blitz')
                ->whereDate('created_at', today())
                ->count();
            $this->canPlay = $this->dailyPlaysUsed < $this->dailyLimit;
        }
    }

    public function saveScore(int $score, int $maxStreak, int $levelReached, array $metadata = []): void
    {
        if (! $this->canPlay) {
            return;
        }

        $user = Auth::user();

        GameScore::create([
            'user_id'       => $user->id,
            'game_slug'     => 'interval-blitz',
            'score'         => $score,
            'max_streak'    => $maxStreak,
            'level_reached' => $levelReached,
            'metadata'      => $metadata ?: null,
        ]);

        $best = GameScore::personalBest($user->id, 'interval-blitz');
        $this->personalBest = $best?->score ?? 0;

        if ($this->dailyLimit !== -1) {
            $this->dailyPlaysUsed++;
            $this->canPlay = $this->dailyPlaysUsed < $this->dailyLimit;
        }

        $this->dispatch('score-saved', isNewBest: $score >= $this->personalBest);
    }

    public function render()
    {
        return view('livewire.games.interval-blitz');
    }
}
