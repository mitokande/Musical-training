<?php

namespace App\Services\Telemetry;

use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One learner at a time: who uses the app, and everything one of them did in
 * it, session by session, screen by screen, tap by tap.
 *
 * A "learner" is an account when there is one — every install it signed in on,
 * and the anonymous onboarding each install did before it (the ingestor files
 * that under the account) — and an install when there is not.
 */
class UserJourneys
{
    public const SEGMENTS = [
        'all' => 'Everyone',
        'signed_in' => 'Signed in',
        'anonymous' => 'Never signed in',
        'purchased' => 'Bought Premium',
        'onboarding_dropout' => 'Left onboarding',
        'errors' => 'Hit an error',
    ];

    /** Sessions per page of a journey. */
    public const SESSIONS_PER_PAGE = 12;

    /** Events shown per session before the rest is summarised. */
    public const EVENTS_PER_SESSION = 400;

    private function json(string $key): string
    {
        return DB::connection()->getQueryGrammar()->wrap('props->'.$key);
    }

    // --- the list ------------------------------------------------------------

    /**
     * Installs active in the range, most recent first, each with what it did
     * there — the way into a journey without already knowing whose to look at.
     */
    public function list(AnalyticsRange $range, string $segment = 'all', string $q = '', int $perPage = 25): LengthAwarePaginator
    {
        $has = fn (string $name, ?callable $where = null) => fn (Builder $query) => $query->whereExists(
            fn ($sub) => $sub->from('app_events')
                ->whereColumn('app_events.install_id', 'app_installs.install_id')
                ->where('app_events.name', $name)
                ->when($where, $where)
        );

        $installs = AppInstall::query()
            ->with('user:id,name,email')
            ->where('last_seen_at', '>=', $range->from)
            ->where('first_seen_at', '<=', $range->to)
            ->when($range->platform, fn ($query) => $query->where('platform', $range->platform))
            ->when($q !== '', function (Builder $query) use ($q) {
                if (preg_match('/^[0-9a-f-]{4,36}$/i', $q) && ! ctype_digit($q)) {
                    $query->where('install_id', 'like', strtolower($q).'%');
                } elseif (ctype_digit($q)) {
                    $query->where('user_id', (int) $q);
                } else {
                    $query->whereHas('user', fn ($user) => $user->where('email', 'like', "%$q%")->orWhere('name', 'like', "%$q%"));
                }
            })
            ->when($segment === 'signed_in', fn ($query) => $query->whereNotNull('user_id'))
            ->when($segment === 'anonymous', fn ($query) => $query->whereNull('user_id'))
            ->when($segment === 'purchased', $has('purchase_result', fn ($sub) => $sub->where('app_events.props->outcome', 'unlocked')))
            ->when($segment === 'onboarding_dropout', fn ($query) => $has('onboarding_step')($query)->whereNotExists(
                fn ($sub) => $sub->from('app_events')
                    ->whereColumn('app_events.install_id', 'app_installs.install_id')
                    ->where('app_events.name', 'onboarding_complete')
            ))
            ->when($segment === 'errors', fn ($query) => $query->whereExists(
                fn ($sub) => $sub->from('app_events')
                    ->whereColumn('app_events.install_id', 'app_installs.install_id')
                    ->whereIn('app_events.name', ['js_error', 'api_error'])
            ))
            ->orderByDesc('last_seen_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = $this->stats(
            AppEvent::query()
                ->whereIn('install_id', $installs->pluck('install_id'))
                ->whereBetween('occurred_at', [$range->from, $range->to]),
            'install_id',
        );

        $onboarded = AppEvent::query()
            ->whereIn('install_id', $installs->pluck('install_id'))
            ->where('name', 'onboarding_complete')
            ->distinct()
            ->pluck('install_id')
            ->flip();

        foreach ($installs as $install) {
            $install->setAttribute('stats', $stats[$install->install_id] ?? null);
            $install->setAttribute('onboarded', isset($onboarded[$install->install_id]));
        }

        return $installs;
    }

    /** Events, sessions, screens, taps, lessons finished, purchases, errors, time in front. */
    private function columns(): string
    {
        $reason = $this->json('reason');
        $outcome = $this->json('outcome');
        $foreground = $this->json('foregroundMs');

        return "COUNT(*) as events, COUNT(DISTINCT session_id) as sessions,
            SUM(CASE WHEN name IN ('screen', 'onboarding_step') THEN 1 ELSE 0 END) as screens,
            SUM(CASE WHEN name = 'tap' THEN 1 ELSE 0 END) as taps,
            SUM(CASE WHEN name = 'lesson_end' AND $reason = 'finished' THEN 1 ELSE 0 END) as lessons,
            SUM(CASE WHEN name = 'purchase_result' AND $outcome = 'unlocked' THEN 1 ELSE 0 END) as purchases,
            SUM(CASE WHEN name = 'paywall_view' THEN 1 ELSE 0 END) as paywalls,
            SUM(CASE WHEN name IN ('js_error', 'api_error') THEN 1 ELSE 0 END) as errors,
            SUM(CASE WHEN name = 'app_background' THEN $foreground ELSE 0 END) as foreground_ms,
            MIN(occurred_at) as first_at, MAX(occurred_at) as last_at";
    }

    /** `columns()` per value of `$by`, keyed by it. */
    private function stats(Builder $query, string $by): Collection
    {
        return $query
            ->selectRaw("$by as grp, ".$this->columns())
            ->groupBy('grp')
            ->toBase()
            ->get()
            ->keyBy('grp');
    }

    // --- one learner ---------------------------------------------------------

    /**
     * Whose journey `q` asks for: a member id, an e-mail address (a deleted
     * account's original one too) or an install id.
     *
     * @return array{user: ?User, install: ?AppInstall}
     */
    public function resolve(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return ['user' => null, 'install' => null];
        }

        if (Str::isUuid($q)) {
            return ['user' => null, 'install' => AppInstall::with('user:id,name,email')->where('install_id', strtolower($q))->first()];
        }

        $user = User::withTrashed()
            ->when(
                ctype_digit($q),
                fn ($query) => $query->whereKey((int) $q),
                fn ($query) => $query->where('email', $q)->orWhere('deleted_email', $q),
            )
            ->first();

        return ['user' => $user, 'install' => null];
    }

    /** Everything the journey page shows above the sessions. */
    public function summary(?User $user, ?AppInstall $install): array
    {
        $scope = fn () => $this->scope($user, $install);

        $totals = $scope()->selectRaw($this->columns())->toBase()->first();

        $first = fn (string $name, array $where = []) => tap($scope()->where('name', $name), function ($query) use ($where) {
            foreach ($where as $key => $value) {
                $query->where('props->'.$key, $value);
            }
        })->min('occurred_at');

        $firstSeen = $totals->first_at ?? null;
        $milestones = collect([
            ['label' => 'First seen', 'icon' => 'download', 'at' => $firstSeen],
            ['label' => 'Finished onboarding', 'icon' => 'sparkles', 'at' => $first('onboarding_complete')],
            ['label' => 'Account', 'icon' => 'user-check', 'at' => $first('auth_success')],
            ['label' => 'First lesson finished', 'icon' => 'flag', 'at' => $first('lesson_end', ['reason' => 'finished'])],
            ['label' => 'First paywall', 'icon' => 'credit-card', 'at' => $first('paywall_view')],
            ['label' => 'Bought Premium', 'icon' => 'badge-check', 'at' => $first('purchase_result', ['outcome' => 'unlocked'])],
        ])->map(function ($milestone) use ($firstSeen) {
            $milestone['after'] = $milestone['at'] && $firstSeen
                ? EventPresenter::duration((int) (CarbonImmutable::parse($firstSeen)->diffInSeconds(CarbonImmutable::parse($milestone['at'])) * 1000))
                : null;

            return $milestone;
        });

        $answers = $scope()->where('name', 'onboarding_complete')->orderByDesc('occurred_at')->value('props');

        $installs = $user
            ? AppInstall::where('user_id', $user->id)
                ->orWhereIn('install_id', AppEvent::where('user_id', $user->id)->select('install_id')->distinct())
                ->orderByDesc('last_seen_at')
                ->get()
            : collect($install ? [$install] : []);

        return [
            'totals' => $totals,
            'milestones' => $milestones,
            'answers' => is_string($answers) ? json_decode($answers, true) : $answers,
            'installs' => $installs,
        ];
    }

    /** Every session, newest first: when, how long, what happened in it. */
    public function sessions(?User $user, ?AppInstall $install): Collection
    {
        return $this->stats($this->scope($user, $install)->whereNotNull('session_id'), 'session_id')
            ->sortByDesc('first_at')
            ->values()
            ->map(function ($row) {
                $row->session_id = $row->grp;
                $row->span_ms = CarbonImmutable::parse($row->first_at)->diffInMilliseconds(CarbonImmutable::parse($row->last_at));

                return $row;
            })
            ->tap(function (Collection $sessions) use ($user, $install) {
                // Device and version per session: the last event's.
                $meta = $this->scope($user, $install)
                    ->whereIn('session_id', $sessions->pluck('session_id'))
                    ->selectRaw('session_id, MAX(platform) as platform, MAX(app_version) as app_version')
                    ->groupBy('session_id')
                    ->toBase()
                    ->get()
                    ->keyBy('session_id');
                foreach ($sessions as $session) {
                    $session->platform = $meta[$session->session_id]->platform ?? null;
                    $session->app_version = $meta[$session->session_id]->app_version ?? null;
                }
            });
    }

    /**
     * The sessions asked for, as visits: each screen shown, how long it was
     * visible, and every event that happened while it was.
     *
     * @param  list<string>  $sessionIds
     * @return array<string, array{visits: list<array>, path: list<string>, truncated: int}>
     */
    public function timelines(?User $user, ?AppInstall $install, array $sessionIds): array
    {
        if ($sessionIds === []) {
            return [];
        }

        $events = $this->scope($user, $install)
            ->whereIn('session_id', $sessionIds)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get(['id', 'session_id', 'name', 'screen', 'props', 'occurred_at'])
            ->groupBy('session_id');

        $timelines = [];
        foreach ($sessionIds as $id) {
            $timelines[$id] = self::timeline($events->get($id, collect()));
        }

        return $timelines;
    }

    /**
     * One session's events folded into visits.
     *
     * A `screen` event (or an onboarding beat) opens a visit; every other event
     * joins the visit it happened in. Visible time stops between leaving the
     * app and coming back, the same way the app measures it.
     */
    public static function timeline(Collection $events): array
    {
        $visits = [];
        $open = null;
        $since = null;
        $truncated = 0;
        // What happened before the session's first screen — its launch, as a
        // rule — shown at the top of that screen rather than as a visit of its own.
        $before = [];
        $start = $events->first()?->occurred_at;

        foreach ($events as $index => $event) {
            if ($index >= self::EVENTS_PER_SESSION) {
                $truncated = $events->count() - self::EVENTS_PER_SESSION;
                break;
            }

            $props = $event->props ?? [];
            $at = CarbonImmutable::parse($event->occurred_at);
            $node = match ($event->name) {
                'screen' => ($event->screen ?? $props['name'] ?? null) === 'onboarding' ? null : ($event->screen ?? $props['name'] ?? null),
                'onboarding_step' => isset($props['beat']) ? 'onboarding/'.$props['beat'] : null,
                default => null,
            };

            if ($node !== null) {
                if ($open !== null && $visits[$open]['screen'] === $node) {
                    continue;
                }
                if ($open !== null && $since !== null) {
                    $visits[$open]['ms'] += (int) $since->diffInMilliseconds($at);
                    $visits[$open]['timed'] = true;
                }
                $visits[] = ['screen' => $node, 'at' => $at, 'offset' => $start ? (int) CarbonImmutable::parse($start)->diffInSeconds($at) : 0, 'ms' => 0, 'timed' => false, 'items' => $before];
                $before = [];
                $open = array_key_last($visits);
                $since = $at;

                continue;
            }

            $item = ['name' => $event->name, 'at' => $at, 'props' => $props] + EventPresenter::describe($event->name, $props);

            if ($open === null) {
                $before[] = $item;

                continue;
            }

            if ($event->name === 'app_background' && $since !== null) {
                $visits[$open]['ms'] += (int) $since->diffInMilliseconds($at);
                $visits[$open]['timed'] = true;
                $since = null;
            } elseif ($event->name === 'app_open' && $since === null) {
                $since = $at;
            }

            $visits[$open]['items'][] = $item;
        }

        // A session that never showed a screen — the app opened and closed.
        if ($before !== []) {
            $visits[] = ['screen' => $before[0]['props']['name'] ?? '—', 'at' => $before[0]['at'], 'offset' => 0, 'ms' => 0, 'timed' => false, 'items' => $before];
        }

        $path = [];
        foreach ($visits as $visit) {
            if ($path === [] || end($path) !== $visit['screen']) {
                $path[] = $visit['screen'];
            }
        }

        return ['visits' => $visits, 'path' => $path, 'truncated' => $truncated];
    }

    private function scope(?User $user, ?AppInstall $install): Builder
    {
        return AppEvent::query()->when(
            $user,
            fn ($query) => $query->where('user_id', $user->id),
            fn ($query) => $query->where('install_id', $install?->install_id ?? ''),
        );
    }
}
