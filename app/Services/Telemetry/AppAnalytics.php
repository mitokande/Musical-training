<?php

namespace App\Services\Telemetry;

use App\Models\AppEvent;
use App\Models\AppInstall;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The numbers behind the admin's Mobile App pages, read off `app_events`.
 *
 * Everything is computed live from the raw stream. That is right while the
 * app is young — no rollup tables to keep in step with a vocabulary that is
 * still changing — and the controller caches each page for a few minutes. When
 * a page gets slow, the fix is a nightly rollup for that page, not a rewrite of
 * the others.
 *
 * Portability matters because the tests run on SQLite and production on
 * MySQL/MariaDB. Every JSON path goes through the connection's own grammar
 * (`json()`), and every JSON boolean is read back raw and normalised in PHP
 * (`truthy()`): MySQL hands a JSON `true` back as the string "true", SQLite as
 * the integer 1, and neither compares equal to the other's.
 *
 * The event vocabulary is the app's — `src/telemetry/events.ts` in
 * harmoniva-mobile — and the property names below are its camelCase.
 */
class AppAnalytics
{
    /** A screen held longer than this was the app in the background, not reading. */
    private const SCREEN_TIME_CAP_MS = 30 * 60 * 1000;

    /** The SQL for a JSON path on `props`, in this connection's dialect. */
    private function json(string $key): string
    {
        return DB::connection()->getQueryGrammar()->wrap('props->'.$key);
    }

    private static function truthy(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true'], true);
    }

    private static function rate(int|float $part, int|float $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }

    // --- overview ------------------------------------------------------------

    public function overview(AnalyticsRange $range): array
    {
        $end = $range->to;
        $activeSince = fn (int $days) => AppEvent::query()
            ->whereBetween('occurred_at', [$end->subDays($days)->addSecond(), $end])
            ->when($range->platform, fn ($q) => $q->where('platform', $range->platform));

        $active = [];
        foreach (['day' => 1, 'week' => 7, 'month' => 30] as $label => $days) {
            $active[$label] = [
                'installs' => $activeSince($days)->distinct()->count('install_id'),
                'users' => $activeSince($days)->whereNotNull('user_id')->distinct()->count('user_id'),
            ];
        }

        $foreground = $this->json('foregroundMs');
        $perSession = $range->events()
            ->where('name', 'app_background')
            ->whereNotNull('session_id')
            ->selectRaw("session_id, SUM($foreground) as total")
            ->groupBy('session_id')
            ->toBase();
        $avgSessionMs = DB::query()->fromSub($perSession, 'sessions')->avg('total');

        return [
            'active' => $active,
            'newInstalls' => $range->installs()->count(),
            'sessions' => $range->events()->whereNotNull('session_id')->distinct()->count('session_id'),
            'avgSessionMinutes' => $avgSessionMs !== null ? round($avgSessionMs / 60000, 1) : null,
            // Not `trend` or `errors`: the view includes stat-card, which reads a
            // `trend` of its own, and `errors` is the validation bag every view
            // is handed — the layout would find this instead.
            'daily' => $this->dailyTrend($range),
            'screens' => $this->screens($range),
            'versions' => $this->versions($range),
            'serverErrors' => $this->errors($range),
        ];
    }

    /** Per day: distinct installs, signed-in users, sessions, and installs first seen. */
    private function dailyTrend(AnalyticsRange $range): array
    {
        $activity = $range->events()
            ->selectRaw('DATE(occurred_at) as day, COUNT(DISTINCT install_id) as installs, COUNT(DISTINCT user_id) as users, COUNT(DISTINCT session_id) as sessions')
            ->groupBy('day')
            ->toBase()
            ->get()
            ->keyBy('day');

        $new = $range->installs()
            ->selectRaw('DATE(first_seen_at) as day, COUNT(*) as installs')
            ->groupBy('day')
            ->toBase()
            ->pluck('installs', 'day');

        // Every day of the window, so a quiet day is a zero on the chart rather
        // than a gap the line quietly skips over.
        $days = [];
        for ($day = $range->from; $day->lte($range->to); $day = $day->addDay()) {
            $key = $day->toDateString();
            $row = $activity->get($key);
            $days[] = [
                'day' => $key,
                'installs' => (int) ($row->installs ?? 0),
                'users' => (int) ($row->users ?? 0),
                'sessions' => (int) ($row->sessions ?? 0),
                'new' => (int) ($new[$key] ?? 0),
            ];
        }

        return $days;
    }

    /**
     * The screens people land on, and how long they stay.
     *
     * The time on a screen is recorded on the *next* screen event, as `prevMs`
     * against `prev`, so the two halves come from two queries keyed the same way.
     */
    private function screens(AnalyticsRange $range): Collection
    {
        $name = $this->json('name');
        $prev = $this->json('prev');
        $prevMs = $this->json('prevMs');

        // `route`, not `screen`: `app_events` has a `screen` column of its own,
        // and MySQL resolves a GROUP BY name against the table before the
        // select list — grouping by the column instead of the alias.
        $views = $range->events()
            ->where('name', 'screen')
            ->selectRaw("$name as route, COUNT(*) as views, COUNT(DISTINCT install_id) as installs")
            ->groupBy('route')
            ->orderByDesc('views')
            ->limit(30)
            ->toBase()
            ->get();

        $dwell = $range->events()
            ->where('name', 'screen')
            ->whereNotNull('props->prev')
            ->whereRaw("$prevMs < ?", [self::SCREEN_TIME_CAP_MS])
            ->selectRaw("$prev as route, AVG($prevMs) as ms")
            ->groupBy('route')
            ->toBase()
            ->pluck('ms', 'route');

        return $views->map(fn ($row) => [
            'screen' => $row->route,
            'views' => (int) $row->views,
            'installs' => (int) $row->installs,
            'avgSeconds' => isset($dwell[$row->route]) ? round($dwell[$row->route] / 1000, 1) : null,
        ]);
    }

    private function versions(AnalyticsRange $range): Collection
    {
        return $range->events()
            ->selectRaw('platform, app_version, COUNT(DISTINCT install_id) as installs')
            ->groupBy('platform', 'app_version')
            ->orderByDesc('installs')
            ->toBase()
            ->get();
    }

    private function errors(AnalyticsRange $range): Collection
    {
        $path = $this->json('path');
        $status = $this->json('status');
        $method = $this->json('method');

        return $range->events()
            ->where('name', 'api_error')
            ->selectRaw("$method as method, $path as path, $status as status, COUNT(*) as count, COUNT(DISTINCT install_id) as installs, MAX(occurred_at) as last_at")
            ->groupBy('method', 'path', 'status')
            ->orderByDesc('count')
            ->limit(15)
            ->toBase()
            ->get();
    }

    // --- funnels -------------------------------------------------------------

    /**
     * From first open to a finished first lesson, for installs first seen in
     * the window.
     *
     * A cohort, not a count of events in the window: someone who installed on
     * the last day and finished onboarding the day after still counts, and a
     * learner re-taking the questions from the profile tab (`firstRun: false`)
     * does not.
     */
    public function onboardingFunnel(AnalyticsRange $range): array
    {
        $cohort = $range->installs()->select('install_id');
        $size = $range->installs()->count();

        $beat = $this->json('beat');
        $index = $this->json('index');
        $firstRun = $this->json('firstRun');

        $beats = AppEvent::query()
            ->where('name', 'onboarding_step')
            ->whereIn('install_id', $cohort)
            ->selectRaw("$beat as beat, $firstRun as first_run, AVG($index) as position, COUNT(DISTINCT install_id) as installs")
            ->groupBy('beat', 'first_run')
            ->toBase()
            ->get()
            ->filter(fn ($row) => self::truthy($row->first_run))
            ->sortBy('position')
            ->values();

        $reached = fn (string $name, array $where = []) => AppEvent::query()
            ->where('name', $name)
            ->whereIn('install_id', $cohort)
            ->where($where)
            ->distinct()
            ->count('install_id');

        $steps = [['label' => 'Opened the app', 'installs' => $size]];
        foreach ($beats as $row) {
            $steps[] = ['label' => 'Onboarding: '.$row->beat, 'installs' => (int) $row->installs];
        }
        $steps[] = ['label' => 'Finished onboarding', 'installs' => $reached('onboarding_complete')];
        $steps[] = ['label' => 'Signed in / created account', 'installs' => $reached('auth_success')];
        $steps[] = ['label' => 'Started a lesson', 'installs' => $reached('lesson_start')];
        $steps[] = ['label' => 'Finished a lesson', 'installs' => $reached('lesson_end', ['props->reason' => 'finished'])];
        $steps[] = ['label' => 'Bought Premium', 'installs' => $reached('purchase_result', ['props->outcome' => 'unlocked'])];

        $previous = null;
        foreach ($steps as &$step) {
            $step['ofStart'] = self::rate($step['installs'], $size);
            $step['ofPrevious'] = $previous === null ? null : self::rate($step['installs'], $previous);
            $previous = $step['installs'];
        }

        return ['cohort' => $size, 'steps' => $steps];
    }

    /**
     * The paywall, door by door: who saw a price, who reached for one, who paid.
     * Distinct installs, so a learner opening it three times is one view.
     */
    public function paywallFunnel(AnalyticsRange $range): Collection
    {
        $from = $this->json('from');
        $state = $this->json('state');
        $outcome = $this->json('outcome');

        $views = $range->events()
            ->where('name', 'paywall_view')
            ->selectRaw("$from as door, $state as state, COUNT(DISTINCT install_id) as installs")
            ->groupBy('door', 'state')
            ->toBase()
            ->get();

        $steps = $range->events()
            ->whereIn('name', ['paywall_plan_selected', 'purchase_start', 'paywall_dismiss'])
            ->selectRaw("$from as door, name, COUNT(DISTINCT install_id) as installs")
            ->groupBy('door', 'name')
            ->toBase()
            ->get();

        $results = $range->events()
            ->where('name', 'purchase_result')
            ->selectRaw("$from as door, $outcome as outcome, COUNT(DISTINCT install_id) as installs")
            ->groupBy('door', 'outcome')
            ->toBase()
            ->get();

        $doors = $views->pluck('door')->merge($steps->pluck('door'))->merge($results->pluck('door'))->unique()->sort()->values();

        return $doors->map(function ($door) use ($views, $steps, $results) {
            $view = fn (string $state) => (int) $views->where('door', $door)->where('state', $state)->sum('installs');
            $step = fn (string $name) => (int) $steps->where('door', $door)->where('name', $name)->sum('installs');
            $result = fn (string $outcome) => (int) $results->where('door', $door)->where('outcome', $outcome)->sum('installs');

            $shown = $view('ready');

            return [
                'door' => $door,
                'shown' => $shown,
                'unavailable' => $view('unavailable'),
                'selected' => $step('paywall_plan_selected'),
                'started' => $step('purchase_start'),
                'unlocked' => $result('unlocked'),
                'cancelled' => $result('cancelled'),
                'failed' => $result('error') + $result('not-unlocked'),
                'dismissed' => $step('paywall_dismiss'),
                'conversion' => self::rate($result('unlocked'), $shown),
            ];
        });
    }

    // --- retention -----------------------------------------------------------

    /**
     * Weekly cohorts by install date, and the classic day-N returns.
     *
     * "Active" is any event at all on that day. Computed in PHP from one row
     * per install per active day, which is cheap at this size and far easier to
     * read than the SQL it would take to do in the database portably.
     *
     * @return array{cohorts: array, days: array}
     */
    public function retention(AnalyticsRange $range, int $weeks = 8): array
    {
        $installs = $range->installs()
            ->get(['install_id', 'first_seen_at'])
            ->mapWithKeys(fn (AppInstall $install) => [
                $install->install_id => CarbonImmutable::parse($install->first_seen_at)->startOfDay(),
            ]);

        $activeDays = [];
        foreach ($installs->keys()->chunk(1000) as $chunk) {
            AppEvent::query()
                ->whereIn('install_id', $chunk->all())
                ->selectRaw('install_id, DATE(occurred_at) as day')
                ->distinct()
                ->toBase()
                ->get()
                ->each(function ($row) use (&$activeDays) {
                    $activeDays[$row->install_id][$row->day] = true;
                });
        }

        $today = CarbonImmutable::now()->startOfDay();
        $offset = fn (CarbonImmutable $first, string $day) => (int) $first->diffInDays(CarbonImmutable::parse($day)->startOfDay());

        // Weekly cohorts, keyed by the Monday of the install week.
        $cohorts = [];
        foreach ($installs as $id => $first) {
            $week = $first->startOfWeek()->toDateString();
            $cohorts[$week] ??= ['week' => $week, 'size' => 0, 'active' => array_fill(0, $weeks + 1, 0)];
            $cohorts[$week]['size']++;

            $seen = [];
            foreach (array_keys($activeDays[$id] ?? []) as $day) {
                $n = intdiv($offset($first, $day), 7);
                // Before the install's first day can only be a clock the
                // skew correction could not rescue; it is not a return.
                if ($n >= 0 && $n <= $weeks && ! isset($seen[$n])) {
                    $seen[$n] = true;
                    $cohorts[$week]['active'][$n]++;
                }
            }
        }
        ksort($cohorts);

        foreach ($cohorts as &$cohort) {
            $monday = CarbonImmutable::parse($cohort['week']);
            $cohort['rates'] = array_map(
                // A week that has not happened yet for this cohort is not a zero.
                fn (int $n) => $monday->addWeeks($n)->gt($today) ? null : self::rate($cohort['active'][$n], $cohort['size']),
                range(0, $weeks),
            );
        }

        // Day N: of the installs old enough to have had a day N, how many came back on it.
        $days = [];
        foreach ([1, 7, 30] as $n) {
            $eligible = $installs->filter(fn (CarbonImmutable $first) => $first->addDays($n)->lte($today));
            $returned = $eligible->filter(fn (CarbonImmutable $first, string $id) => isset($activeDays[$id][$first->addDays($n)->toDateString()]));
            $days[$n] = ['eligible' => $eligible->count(), 'returned' => $returned->count(), 'rate' => self::rate($returned->count(), $eligible->count())];
        }

        return ['cohorts' => array_values($cohorts), 'days' => $days, 'weeks' => $weeks];
    }

    // --- learning ------------------------------------------------------------

    /** Per lesson: how often it is started, finished, abandoned, and how it goes. */
    public function lessons(AnalyticsRange $range): Collection
    {
        $node = $this->json('nodeId');
        $kind = $this->json('kind');
        $reason = $this->json('reason');
        $score = $this->json('score');
        $seconds = $this->json('seconds');
        $step = $this->json('step');
        $steps = $this->json('steps');

        $starts = $range->events()
            ->where('name', 'lesson_start')
            ->selectRaw("$node as node, $kind as kind, COUNT(*) as count, COUNT(DISTINCT install_id) as installs")
            ->groupBy('node', 'kind')
            ->toBase()
            ->get();

        $ends = $range->events()
            ->where('name', 'lesson_end')
            ->selectRaw("$node as node, $reason as reason, COUNT(*) as count, AVG($score) as score, AVG($seconds) as seconds, AVG($step) as step, AVG($steps) as steps")
            ->groupBy('node', 'reason')
            ->toBase()
            ->get();

        $nodes = $starts->pluck('node')->merge($ends->pluck('node'))->unique()->sort(SORT_NATURAL)->values();

        return $nodes->map(function ($node) use ($starts, $ends) {
            $graded = $starts->where('node', $node)->whereIn('kind', ['lesson', 'checkpoint']);
            $end = fn (string $reason) => $ends->where('node', $node)->firstWhere('reason', $reason);

            $started = (int) $graded->sum('count');
            $finished = $end('finished');
            $abandoned = $end('abandoned');
            $outOfHearts = $end('out-of-hearts');

            return [
                'node' => $node,
                'checkpoint' => $graded->contains('kind', 'checkpoint'),
                'started' => $started,
                'learners' => (int) $graded->sum('installs'),
                'reviews' => (int) $starts->where('node', $node)->where('kind', 'review')->sum('count'),
                'finished' => (int) ($finished->count ?? 0),
                'abandoned' => (int) ($abandoned->count ?? 0),
                'outOfHearts' => (int) ($outOfHearts->count ?? 0),
                'completion' => self::rate((int) ($finished->count ?? 0), $started),
                'avgScore' => isset($finished->score) ? round((float) $finished->score) : null,
                'avgMinutes' => isset($finished->seconds) ? round($finished->seconds / 60, 1) : null,
                // Where in the lesson people give up, as a share of its steps.
                'quitAt' => isset($abandoned->steps) && $abandoned->steps > 0
                    ? round($abandoned->step / $abandoned->steps * 100)
                    : null,
            ];
        });
    }

    /**
     * Per interval: first-try accuracy, replays, time to answer — and which
     * interval it is mistaken for.
     *
     * Identify tasks only for the confusion: their answer is another concept
     * id, so "picked P4 when it was P5" is exactly what `chosen` says. Build
     * tasks answer with a note, which is a different question, and are counted
     * separately. First attempts only: a second try after being told "wrong"
     * measures something else.
     */
    public function concepts(AnalyticsRange $range): array
    {
        $concept = $this->json('conceptId');
        $chosen = $this->json('chosen');
        $correct = $this->json('correct');
        $replays = $this->json('replays');
        $elapsed = $this->json('elapsedMs');

        $firstTries = fn () => $range->events()
            ->where('name', 'task_answered')
            ->where('props->attemptNo', 1);

        $identify = $firstTries()
            ->where('props->kind', 'identify')
            ->selectRaw("$concept as concept, $chosen as chosen, COUNT(*) as count, AVG($replays) as replays, AVG($elapsed) as ms")
            ->groupBy('concept', 'chosen')
            ->toBase()
            ->get();

        $build = $firstTries()
            ->where('props->kind', 'build')
            ->selectRaw("$concept as concept, $correct as correct, COUNT(*) as count")
            ->groupBy('concept', 'correct')
            ->toBase()
            ->get();

        $concepts = $identify->pluck('concept')->merge($build->pluck('concept'))->unique()->sort(SORT_NATURAL)->values();

        $rows = $concepts->map(function ($id) use ($identify, $build) {
            $answers = $identify->where('concept', $id);
            $total = (int) $answers->sum('count');
            $right = (int) $answers->where('chosen', $id)->sum('count');
            $mistake = $answers->where('chosen', '!=', $id)->sortByDesc('count')->first();

            $built = $build->where('concept', $id);
            $builtTotal = (int) $built->sum('count');
            $builtRight = (int) $built->filter(fn ($row) => self::truthy($row->correct))->sum('count');

            return [
                'concept' => $id,
                'answers' => $total,
                'accuracy' => self::rate($right, $total),
                'avgReplays' => $total > 0 ? round($answers->sum(fn ($row) => $row->replays * $row->count) / $total, 2) : null,
                'avgSeconds' => $total > 0 ? round($answers->sum(fn ($row) => $row->ms * $row->count) / $total / 1000, 1) : null,
                'topConfusion' => $mistake ? ['chosen' => $mistake->chosen, 'share' => self::rate((int) $mistake->count, $total)] : null,
                'builds' => $builtTotal,
                'buildAccuracy' => self::rate($builtRight, $builtTotal),
            ];
        });

        // The matrix: rows are what played, columns what was picked, cells the
        // share of that row's answers. Columns include any answer ever given,
        // in case the app offers a choice that is not itself asked about.
        $columns = $concepts->merge($identify->pluck('chosen'))->unique()->sort(SORT_NATURAL)->values();
        $matrix = [];
        foreach ($concepts as $id) {
            $total = (int) $identify->where('concept', $id)->sum('count');
            if ($total === 0) {
                continue;
            }
            foreach ($columns as $column) {
                $count = (int) $identify->where('concept', $id)->where('chosen', $column)->sum('count');
                $matrix[$id][$column] = $count > 0 ? self::rate($count, $total) : null;
            }
        }

        return ['rows' => $rows, 'columns' => $columns, 'matrix' => $matrix];
    }
}
