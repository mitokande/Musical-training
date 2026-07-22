<?php

namespace App\Services;

use App\Models\UsageCounter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Central usage-quota ledger for guests and authenticated users.
 *
 * All daily/lifetime counters (learning-path sessions, studio sessions,
 * guest game plays, teacher messages, ...) go through this service so the
 * same feature is never counted in two different places. Limits themselves
 * live in config/plans.php (guests: plans.guest.*, users via
 * User::getPlanLimit()); this class only tracks consumption.
 *
 * Guests are identified by a long-lived signed cookie so limits survive the
 * session; all guest limits reset daily (period = today).
 */
class UsageQuotaService
{
    public const GUEST_COOKIE = 'harmoniva_guest_id';

    public const PERIOD_TOTAL = 'total';

    // Feature keys — single source of truth for counter names.
    public const FEATURE_LP_SESSIONS = 'lp_sessions';

    public const FEATURE_STUDIO_SESSIONS = 'studio_sessions';

    public const FEATURE_TEACHER_MESSAGES = 'teacher_messages';

    public const FEATURE_GAME_PREFIX = 'game_'; // game_{slug}

    /** Resolve (or mint) the server-side guest identifier for this request. */
    public function guestIdentifier(Request $request): string
    {
        $id = $request->cookie(self::GUEST_COOKIE) ?: session(self::GUEST_COOKIE);

        if (! is_string($id) || ! preg_match('/^[0-9a-f-]{36}$/', $id)) {
            $id = (string) Str::uuid();
        }

        // Persist in both cookie (1 year) and session so a blocked cookie
        // still yields a stable id within the browsing session.
        session([self::GUEST_COOKIE => $id]);
        Cookie::queue(Cookie::make(self::GUEST_COOKIE, $id, 60 * 24 * 365));

        return $id;
    }

    // ── Guests (all guest limits are daily) ────────────────────────────────

    public function guestUsed(Request $request, string $feature): int
    {
        return $this->used('guest', $this->guestIdentifier($request), $feature, $this->today());
    }

    public function guestIncrement(Request $request, string $feature): void
    {
        $this->increment('guest', $this->guestIdentifier($request), $feature, $this->today());
    }

    public function guestRemaining(Request $request, string $feature, string $limitKey): int
    {
        $limit = (int) config('plans.guest.'.$limitKey, 0);

        if ($limit === -1) {
            return PHP_INT_MAX;
        }

        return max(0, $limit - $this->guestUsed($request, $feature));
    }

    // ── Authenticated users (daily unless $period given) ───────────────────

    public function userUsed(User $user, string $feature, ?string $period = null): int
    {
        return $this->used('user', (string) $user->id, $feature, $period ?? $this->today());
    }

    public function userIncrement(User $user, string $feature, ?string $period = null): void
    {
        $this->increment('user', (string) $user->id, $feature, $period ?? $this->today());
    }

    /**
     * Remaining daily quota for a plan-limited feature. -1 limits (and admins)
     * mean unlimited. $limitKey is a config/plans.php key resolved through
     * User::getPlanLimit() so DB-managed plan overrides keep working.
     */
    public function userRemaining(User $user, string $feature, string $limitKey): int
    {
        if ($user->isAdmin()) {
            return PHP_INT_MAX;
        }

        $limit = $user->getPlanLimit($limitKey);

        if ($limit === null || (int) $limit === -1) {
            return PHP_INT_MAX;
        }

        return max(0, (int) $limit - $this->userUsed($user, $feature));
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function today(): string
    {
        return now()->toDateString();
    }

    private function used(string $type, string $id, string $feature, string $period): int
    {
        return (int) (UsageCounter::query()
            ->where('subject_type', $type)
            ->where('subject_id', $id)
            ->where('feature', $feature)
            ->where('period', $period)
            ->value('count') ?? 0);
    }

    private function increment(string $type, string $id, string $feature, string $period): void
    {
        $counter = UsageCounter::firstOrCreate(
            ['subject_type' => $type, 'subject_id' => $id, 'feature' => $feature, 'period' => $period],
            ['count' => 0]
        );

        $counter->increment('count');
    }
}
