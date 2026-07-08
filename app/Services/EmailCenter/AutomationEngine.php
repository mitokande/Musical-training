<?php

namespace App\Services\EmailCenter;

use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\ExerciseSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Runs the standard lifecycle automations. Each enabled automation finds
 * its due users, applies cooldowns and the shared frequency cap (via
 * EmailDispatchService) and queues at most $batchLimit sends per run so a
 * newly enabled automation cannot flood the queue.
 */
class AutomationEngine
{
    public const KEYS = [
        'welcome',
        'first_exercise_reminder',
        'learning_path_reminder',
        'weekly_progress',
        're_engagement',
        'premium_upsell',
    ];

    protected int $batchLimit = 200;

    public function __construct(protected EmailDispatchService $dispatcher) {}

    public function run(): array
    {
        $results = [];

        foreach (EmailAutomation::where('enabled', true)->whereNotNull('template_id')->with('template')->get() as $automation) {
            if (! $automation->template?->is_active) {
                continue;
            }

            $sent = 0;

            foreach ($this->dueUsers($automation)->limit($this->batchLimit)->get() as $user) {
                $message = $this->dispatcher->dispatch(
                    recipient: $user,
                    emailType: 'automation',
                    template: $automation->template,
                    automation: $automation,
                    context: $this->context($automation, $user),
                );

                if ($message) {
                    $sent++;
                }
            }

            $automation->update(['last_run_at' => now()]);
            $results[$automation->key] = $sent;

            if ($sent > 0) {
                Log::info('Email automation run', ['key' => $automation->key, 'queued' => $sent]);
            }
        }

        return $results;
    }

    protected function dueUsers(EmailAutomation $automation): Builder
    {
        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->whereNull('suspended_at')
            ->where('is_restricted', false)
            ->where('role', 'user');

        switch ($automation->key) {
            case 'welcome':
                $delay = (int) $automation->configValue('delay_hours', 1);
                $query->where('created_at', '<=', now()->subHours($delay))
                    // never greet accounts older than a week (first-enable safety)
                    ->where('created_at', '>=', now()->subDays(7));
                $this->neverReceived($query, $automation);
                break;

            case 'first_exercise_reminder':
                $days = (int) $automation->configValue('delay_days', 2);
                $query->where('created_at', '<=', now()->subDays($days))
                    ->where('created_at', '>=', now()->subDays(30))
                    ->whereDoesntHave('exerciseSessions');
                $this->neverReceived($query, $automation);
                break;

            case 'learning_path_reminder':
                $inactive = (int) $automation->configValue('inactive_days', 7);
                $query->where('last_active_at', '<', now()->subDays($inactive))
                    ->whereExists(function ($sub) {
                        $sub->selectRaw('1')->from('user_learning_path_progress')
                            ->whereColumn('user_learning_path_progress.user_id', 'users.id');
                    });
                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 14));
                break;

            case 'weekly_progress':
                $query->where('last_active_at', '>=', now()->subDays(7))
                    ->whereHas('exerciseSessions', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)));
                $this->notReceivedWithin($query, $automation, 6);
                break;

            case 're_engagement':
                $inactive = (int) $automation->configValue('inactive_days', 30);
                $query->where('last_active_at', '<', now()->subDays($inactive));
                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 60));
                break;

            case 'premium_upsell':
                $query->where('plan', 'free')
                    ->where('created_at', '<=', now()->subDays((int) $automation->configValue('min_account_days', 14)))
                    ->whereHas('exerciseSessions', fn ($q) => $q, '>=', (int) $automation->configValue('min_sessions', 10));
                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 30));
                break;

            default:
                // unknown key — match nobody
                $query->whereRaw('1 = 0');
        }

        return $query->orderBy('id');
    }

    protected function neverReceived(Builder $query, EmailAutomation $automation): void
    {
        $query->whereNotIn('id', EmailMessage::where('automation_id', $automation->id)
            ->whereNotNull('user_id')->select('user_id'));
    }

    protected function notReceivedWithin(Builder $query, EmailAutomation $automation, int $days): void
    {
        $query->whereNotIn('id', EmailMessage::where('automation_id', $automation->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('user_id')->select('user_id'));
    }

    /**
     * Template variables specific to each automation.
     */
    protected function context(EmailAutomation $automation, User $user): array
    {
        if ($automation->key !== 'weekly_progress') {
            return [];
        }

        $sessions = ExerciseSession::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        return [
            'weekly_sessions' => (string) $sessions->count(),
            'weekly_accuracy' => $sessions->avg('accuracy') !== null ? round($sessions->avg('accuracy')).'%' : '—',
            'weekly_minutes' => (string) (int) round($sessions->sum('duration_seconds') / 60),
        ];
    }
}
