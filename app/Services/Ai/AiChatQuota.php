<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * The daily "Ask AI" allowance, shared by the website and the mobile app.
 *
 * The counter key is deliberately the one AiChatController already writes —
 * `ai_chat:{id}:{Y-m-d}` — so a question asked on harmoniva.app and a question
 * asked in the app draw down the same allowance. A separate mobile key would
 * silently hand every user double the plan's limit for owning a phone.
 *
 * The limits themselves stay where they already are: the plan matrix under
 * `ask_ai_daily` (config/plans.php, overridable per Plan row) — free 1/day,
 * premium 10/day, -1 unlimited.
 */
class AiChatQuota
{
    public const FEATURE = 'ask_ai_daily';

    /** -1 means unlimited, matching the plan matrix's own convention. */
    public function limitFor(User $user): int
    {
        // Mirrors UsageQuotaService::userRemaining() and User::canAccess(),
        // which exempt admins from every other metered feature. config/plans.php
        // has no `admin` block at all, so without this an admin would fall
        // through to the null default and be metered at one question a day.
        if ($user->isAdmin()) {
            return -1;
        }

        $limit = $user->getPlanLimit(self::FEATURE);

        return $limit === null ? 1 : (int) $limit;
    }

    public function used(User $user): int
    {
        return (int) Cache::get($this->key($user), 0);
    }

    public function increment(User $user): void
    {
        // Expires at midnight rather than after 24h, so the allowance resets on
        // the calendar day the "resets_at" in the snapshot promises. Same
        // expiry the web controller writes, since it is the same key.
        Cache::put($this->key($user), $this->used($user) + 1, now()->endOfDay());
    }

    public function isExhausted(User $user): bool
    {
        $limit = $this->limitFor($user);

        return $limit !== -1 && $this->used($user) >= $limit;
    }

    /**
     * What the app renders as the "2 / 10 today" badge. Field names match the
     * `daily` block already returned by the practice-types catalog, so the
     * client reads both with one type.
     *
     * @return array{limit: int, unlimited: bool, used: int, remaining: int|null, resets_at: string}
     */
    public function snapshot(User $user): array
    {
        $limit = $this->limitFor($user);
        $used = $this->used($user);
        $unlimited = $limit === -1;

        return [
            'limit' => $limit,
            'unlimited' => $unlimited,
            'used' => $used,
            'remaining' => $unlimited ? null : max(0, $limit - $used),
            'resets_at' => now()->endOfDay()->toIso8601String(),
        ];
    }

    private function key(User $user): string
    {
        return "ai_chat:{$user->id}:".now()->toDateString();
    }
}
