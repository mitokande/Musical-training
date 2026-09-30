<?php

namespace App\Services\Telemetry;

use Illuminate\Support\Facades\DB;

/**
 * Journeys: what learners do, in order, read off `app_events` one session at a
 * time.
 *
 * A session becomes a list of steps. A step is a screen — the route pattern,
 * or for onboarding the beat (`onboarding/goals`), because that one route is
 * where most of the flow's decisions are — and, when asked for, a milestone:
 * an event that changes what a learner *is* (signed in, bought Premium).
 * Consecutive repeats collapse, so a screen that re-rendered is one visit.
 *
 * Each screen step knows how long it was visible, measured the way the app
 * measures it: until the next step, less any stretch the app spent in the
 * background between `app_background` and `app_open`. The last step of a
 * session that ended with the app killed rather than backgrounded has no
 * known length and is left out of every average.
 *
 * Everything streams. Sessions arrive ordered, each is folded into running
 * totals and dropped, so memory holds one session at a time however wide the
 * range. A session that began before the range is seen from where the range
 * starts. The controller caches every result for a few minutes; when that is
 * no longer enough, the next step is a nightly rollup of sessions into their
 * own table, fed by this same fold.
 */
class JourneyAnalytics
{
    public const START = '(start)';

    public const EXIT = '(exit)';

    public const OTHER = '(other)';

    /** Events that are a turn in the story, with the condition that makes them one. */
    public const MILESTONES = [
        'auth_success' => '★ Signed in',
        'onboarding_complete' => '★ Finished onboarding',
        'lesson_end' => '★ Finished a lesson',
        'purchase_result' => '★ Bought Premium',
    ];

    /** Routes that are several screens in one; their `screen` event is replaced by their beats. */
    private const EXPANDED_ROUTES = ['onboarding'];

    /** A step visible longer than this was left open in front of nobody. */
    private const DWELL_CAP_MS = 30 * 60 * 1000;

    /** Steps kept per session: a journey longer than this is a long session, not a longer answer. */
    private const MAX_STEPS = 300;

    /**
     * Dwell samples kept per screen for its median. Sessions stream in the
     * order of their (random) uuids, so the first N are a fair sample.
     */
    private const DWELL_SAMPLES = 2000;

    /** Bounds of the dwell histogram, in seconds, and what each bucket is called. */
    public const DWELL_BUCKETS = [3, 10, 30, 60, 180, 600];

    public const DWELL_LABELS = ['< 3s', '3–10s', '10–30s', '30s–1m', '1–3m', '3–10m', '> 10m'];

    private function json(string $key): string
    {
        return DB::connection()->getQueryGrammar()->wrap('props->'.$key);
    }

    private static function rate(int|float $part, int|float $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }

    /**
     * Epoch ms from a stored `occurred_at`, fast: this runs once per row of a
     * streamed range, where parsing each one into a Carbon was most of the cost.
     * Only differences are ever taken, so the zone only has to be consistent.
     */
    private static function ms(string $datetime): int
    {
        $fraction = strlen($datetime) > 20 ? (int) str_pad(substr($datetime, 20, 3), 3, '0') : 0;

        return strtotime(substr($datetime, 0, 19).' UTC') * 1000 + $fraction;
    }

    public static function isMilestone(string $node): bool
    {
        return str_starts_with($node, '★');
    }

    // --- the fold ------------------------------------------------------------

    /**
     * Calls `$visit($install, $steps)` once per session in the range, and
     * returns how many there were.
     *
     * A step is `['node' => string, 'at' => int epoch ms, 'ms' => ?int]`,
     * `ms` being visible time — null for a milestone, and for a last step
     * whose end nobody saw.
     *
     * @param  callable(string, list<array{node: string, at: int, ms: ?int}>): void  $visit
     */
    public function eachSession(AnalyticsRange $range, bool $milestones, callable $visit): int
    {
        $names = ['screen', 'onboarding_step', 'app_background', 'app_open'];
        if ($milestones) {
            array_push($names, ...array_keys(self::MILESTONES));
        }

        $rows = $range->events()
            ->whereIn('name', $names)
            ->whereNotNull('session_id')
            ->select(['id', 'session_id', 'install_id', 'name', 'screen', 'occurred_at'])
            ->selectRaw($this->json('name').' as route, '.$this->json('beat').' as beat, '.$this->json('reason').' as reason, '.$this->json('outcome').' as outcome')
            ->orderBy('session_id')
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->toBase()
            ->cursor();

        $sessions = 0;
        $session = null;
        $install = '';
        $steps = [];
        $open = null;   // index of the screen step whose clock is running
        $since = null;  // when that clock last started, epoch ms; null while backgrounded

        $finish = function () use (&$session, &$install, &$steps, &$open, &$since, &$sessions, $visit) {
            if ($session !== null && $steps !== []) {
                // Still running when the session ran out: nobody saw it end.
                if ($open !== null && $since !== null) {
                    $steps[$open]['ms'] = null;
                }
                foreach ($steps as &$step) {
                    if ($step['ms'] !== null) {
                        $step['ms'] = min($step['ms'], self::DWELL_CAP_MS);
                    }
                }
                unset($step);
                $visit($install, $steps);
                $sessions++;
            }
            $steps = [];
            $open = null;
            $since = null;
        };

        foreach ($rows as $row) {
            if ($row->session_id !== $session) {
                $finish();
                $session = $row->session_id;
                $install = $row->install_id;
            }

            $at = self::ms((string) $row->occurred_at);

            if ($row->name === 'app_background') {
                if ($open !== null && $since !== null) {
                    $steps[$open]['ms'] += max(0, $at - $since);
                }
                $since = null;

                continue;
            }
            if ($row->name === 'app_open') {
                if ($open !== null && $since === null) {
                    $since = $at;
                }

                continue;
            }

            $node = $this->node($row);
            if ($node === null || count($steps) >= self::MAX_STEPS) {
                continue;
            }
            if ($steps !== [] && $steps[array_key_last($steps)]['node'] === $node) {
                continue;
            }

            if (self::isMilestone($node)) {
                $steps[] = ['node' => $node, 'at' => $at, 'ms' => null];

                continue;
            }

            if ($open !== null && $since !== null) {
                $steps[$open]['ms'] += max(0, $at - $since);
            }
            $steps[] = ['node' => $node, 'at' => $at, 'ms' => 0];
            $open = array_key_last($steps);
            $since = $at;
        }
        $finish();

        return $sessions;
    }

    /** The step an event stands for, or null when it stands for none. */
    private function node(object $row): ?string
    {
        switch ($row->name) {
            case 'screen':
                $node = $row->screen ?? $row->route;

                return is_string($node) && $node !== '' && ! in_array($node, self::EXPANDED_ROUTES, true) ? $node : null;
            case 'onboarding_step':
                return is_string($row->beat) && $row->beat !== '' ? 'onboarding/'.$row->beat : null;
            case 'lesson_end':
                return $row->reason === 'finished' ? self::MILESTONES['lesson_end'] : null;
            case 'purchase_result':
                return $row->outcome === 'unlocked' ? self::MILESTONES['purchase_result'] : null;
            default:
                return self::MILESTONES[$row->name] ?? null;
        }
    }

    /** The screen steps of a session alone, repeats collapsed again once the milestones are gone. */
    private static function screensOnly(array $steps): array
    {
        $screens = [];
        foreach ($steps as $step) {
            if (self::isMilestone($step['node'])) {
                continue;
            }
            if ($screens !== [] && $screens[array_key_last($screens)]['node'] === $step['node']) {
                continue;
            }
            $screens[] = $step;
        }

        return $screens;
    }

    // --- screens -------------------------------------------------------------

    /**
     * Every screen: how often it is seen, how long for, how sessions arrive at
     * it and leave from it, and where they go next.
     *
     * @return array{sessions: int, screens: array<string, array>, transitions: array<string, array<string, int>>}
     */
    public function screens(AnalyticsRange $range): array
    {
        $stats = [];
        $transitions = [];

        $sessions = $this->eachSession($range, false, function (string $install, array $steps) use (&$stats, &$transitions) {
            $steps = self::screensOnly($steps);
            $last = count($steps) - 1;

            foreach ($steps as $i => $step) {
                $node = $step['node'];
                $stats[$node] ??= ['views' => 0, 'entries' => 0, 'exits' => 0, 'timed' => 0, 'totalMs' => 0, 'samples' => [], 'buckets' => array_fill(0, count(self::DWELL_BUCKETS) + 1, 0)];
                $s = &$stats[$node];
                $s['views']++;
                if ($i === 0) {
                    $s['entries']++;
                }

                $next = $i === $last ? self::EXIT : $steps[$i + 1]['node'];
                if ($i === $last) {
                    $s['exits']++;
                }
                $transitions[$node][$next] = ($transitions[$node][$next] ?? 0) + 1;

                if ($step['ms'] !== null) {
                    $s['timed']++;
                    $s['totalMs'] += $step['ms'];
                    if (count($s['samples']) < self::DWELL_SAMPLES) {
                        $s['samples'][] = $step['ms'];
                    }
                    $s['buckets'][self::bucket($step['ms'])]++;
                }
                unset($s);
            }
        });

        $viewers = $this->viewers($range);
        $taps = $this->tapsPerScreen($range);

        $screens = [];
        foreach ($stats as $node => $s) {
            sort($s['samples']);
            $screens[$node] = [
                'screen' => $node,
                'views' => $s['views'],
                'viewers' => $viewers[$node] ?? null,
                'entries' => $s['entries'],
                'exits' => $s['exits'],
                'exitRate' => self::rate($s['exits'], $s['views']),
                'avgSeconds' => $s['timed'] > 0 ? round($s['totalMs'] / $s['timed'] / 1000, 1) : null,
                'medianSeconds' => $s['samples'] !== [] ? round($s['samples'][intdiv(count($s['samples']), 2)] / 1000, 1) : null,
                'buckets' => $s['buckets'],
                'taps' => $taps[$node]['taps'] ?? 0,
                'tappers' => $taps[$node]['tappers'] ?? 0,
            ];
        }
        uasort($screens, fn ($a, $b) => $b['views'] <=> $a['views']);

        foreach ($transitions as &$row) {
            arsort($row);
        }
        unset($row);

        return ['sessions' => $sessions, 'screens' => $screens, 'transitions' => $transitions];
    }

    private static function bucket(int $ms): int
    {
        foreach (self::DWELL_BUCKETS as $i => $bound) {
            if ($ms < $bound * 1000) {
                return $i;
            }
        }

        return count(self::DWELL_BUCKETS);
    }

    /** Distinct installs that saw each screen: routes from `screen`, onboarding beats from `onboarding_step`. */
    private function viewers(AnalyticsRange $range): array
    {
        $route = $this->json('name');
        $beat = $this->json('beat');

        $routes = $range->events()
            ->where('name', 'screen')
            ->selectRaw("COALESCE(screen, $route) as node, COUNT(DISTINCT install_id) as installs")
            ->groupBy('node')
            ->toBase()
            ->pluck('installs', 'node');

        $beats = $range->events()
            ->where('name', 'onboarding_step')
            ->selectRaw("$beat as beat, COUNT(DISTINCT install_id) as installs")
            ->groupBy('beat')
            ->toBase()
            ->pluck('installs', 'beat')
            ->mapWithKeys(fn ($installs, $beat) => ['onboarding/'.$beat => $installs]);

        return $routes->merge($beats)->map(fn ($n) => (int) $n)->all();
    }

    /** Taps and distinct tappers per screen. */
    private function tapsPerScreen(AnalyticsRange $range): array
    {
        return $range->events()
            ->where('name', 'tap')
            ->whereNotNull('screen')
            ->selectRaw('screen, COUNT(*) as taps, COUNT(DISTINCT install_id) as tappers')
            ->groupBy('screen')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [$row->screen => ['taps' => (int) $row->taps, 'tappers' => (int) $row->tappers]])
            ->all();
    }

    /**
     * What gets tapped, by screen: each control's taps, how many installs
     * tapped it, and — against the screen's viewers — how many of the people
     * who saw it reached for it.
     *
     * @param  array<string, int>  $viewers  distinct installs per screen, from `screens()`
     */
    public function taps(AnalyticsRange $range, ?string $screen = null, array $viewers = [], int $limit = 100): array
    {
        $id = $this->json('id');

        return $range->events()
            ->where('name', 'tap')
            ->when($screen !== null, fn ($q) => $q->where('screen', $screen))
            ->selectRaw("screen, $id as control, COUNT(*) as taps, COUNT(DISTINCT install_id) as tappers")
            ->groupBy('screen', 'control')
            ->orderByDesc('taps')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'screen' => $row->screen,
                'control' => $row->control,
                'taps' => (int) $row->taps,
                'tappers' => (int) $row->tappers,
                'reach' => isset($viewers[$row->screen]) ? self::rate((int) $row->tappers, $viewers[$row->screen]) : null,
            ])
            ->all();
    }

    // --- flows ---------------------------------------------------------------

    /**
     * The Sankey: from a step, the next `depth` steps sessions took — or,
     * looking `before`, the `depth` steps that led there.
     *
     * Each column keeps its `width` busiest steps; the rest fold into
     * "(other)", so the picture stays readable however many screens there are.
     * A session is counted from the first time it reaches `from`; `from` null
     * starts every session at its own beginning.
     *
     * @return array{sessions: int, matched: int, nodes: list<array>, links: list<array>, columns: list<array>}
     */
    public function flow(AnalyticsRange $range, ?string $from, string $direction = 'after', int $depth = 4, int $width = 7, bool $milestones = false): array
    {
        $before = $direction === 'before';
        if ($before && $from === null) {
            $before = false;
        }

        $paths = [];
        $sessions = $this->eachSession($range, $milestones, function (string $install, array $steps) use (&$paths, $from, $before, $depth) {
            $nodes = array_column($steps, 'node');

            if ($from === null) {
                $window = array_merge([self::START], array_slice($nodes, 0, $depth));
                if (count($nodes) <= $depth) {
                    $window[] = self::EXIT;
                }
            } else {
                $at = array_search($from, $nodes, true);
                if ($at === false) {
                    return;
                }
                if ($before) {
                    $start = max(0, $at - $depth);
                    $window = array_slice($nodes, $start, $at - $start + 1);
                    if ($start === 0) {
                        array_unshift($window, self::START);
                    }
                } else {
                    $window = array_slice($nodes, $at, $depth + 1);
                    if ($at + $depth >= count($nodes)) {
                        $window[] = self::EXIT;
                    }
                }
            }

            $key = implode("\u{1F}", $window);
            $paths[$key] = ($paths[$key] ?? 0) + 1;
        });

        // Align every path to the column of the anchor: after → it is column
        // 0 and paths run right; before → it is the last column and paths run
        // left, so a short path ends at the anchor, not at column 0. Two
        // columns beyond the depth: the anchor itself and an end marker.
        $columns = $depth + 2;
        $counts = array_fill(0, $columns, []);
        $aligned = [];
        foreach ($paths as $key => $n) {
            $steps = explode("\u{1F}", $key);
            $offset = $before ? $columns - count($steps) : 0;
            $aligned[] = [$offset, $steps, $n];
            foreach ($steps as $i => $step) {
                $counts[$offset + $i][$step] = ($counts[$offset + $i][$step] ?? 0) + $n;
            }
        }

        // The busiest `width` per column survive; the end markers always do.
        $keep = [];
        foreach ($counts as $c => $column) {
            arsort($column);
            $keep[$c] = [];
            foreach (array_keys($column) as $i => $step) {
                if ($i < $width || in_array($step, [self::START, self::EXIT], true) || $step === $from) {
                    $keep[$c][$step] = true;
                }
            }
        }

        $nodes = [];
        $links = [];
        $matched = 0;
        foreach ($aligned as [$offset, $steps, $n]) {
            $matched += $n;
            $previous = null;
            foreach ($steps as $i => $step) {
                $c = $offset + $i;
                $label = isset($keep[$c][$step]) ? $step : self::OTHER;
                $id = $c.'|'.$label;
                $nodes[$id] ??= ['id' => $id, 'label' => $label, 'column' => $c, 'value' => 0];
                $nodes[$id]['value'] += $n;
                if ($previous !== null) {
                    $link = $previous.'>'.$id;
                    $links[$link] ??= ['source' => $previous, 'target' => $id, 'value' => 0];
                    $links[$link]['value'] += $n;
                }
                $previous = $id;
            }
        }

        $byColumn = [];
        foreach ($nodes as $node) {
            $byColumn[$node['column']][] = ['label' => $node['label'], 'sessions' => $node['value'], 'share' => self::rate($node['value'], $matched)];
        }
        ksort($byColumn);
        foreach ($byColumn as &$column) {
            usort($column, fn ($a, $b) => $b['sessions'] <=> $a['sessions']);
        }
        unset($column);

        return [
            'sessions' => $sessions,
            'matched' => $matched,
            'nodes' => array_values($nodes),
            'links' => array_values($links),
            'columns' => array_values($byColumn),
        ];
    }

    /**
     * The most common openings: each session's first `length` steps as one
     * path, and how many sessions took exactly that path.
     *
     * @return array{sessions: int, paths: list<array{steps: list<string>, sessions: int, share: ?float, complete: bool}>}
     */
    public function topPaths(AnalyticsRange $range, int $length = 5, int $limit = 25, bool $milestones = false): array
    {
        $paths = [];
        $sessions = $this->eachSession($range, $milestones, function (string $install, array $steps) use (&$paths, $length) {
            $nodes = array_column($steps, 'node');
            $window = array_slice($nodes, 0, $length);
            if (count($nodes) <= $length) {
                $window[] = self::EXIT;
            }
            $key = implode("\u{1F}", $window);
            $paths[$key] = ($paths[$key] ?? 0) + 1;
        });

        arsort($paths);

        $top = [];
        foreach (array_slice($paths, 0, $limit, true) as $key => $n) {
            $steps = explode("\u{1F}", (string) $key);
            $top[] = [
                'steps' => $steps,
                'sessions' => $n,
                'share' => self::rate($n, $sessions),
                'complete' => end($steps) === self::EXIT,
            ];
        }

        return ['sessions' => $sessions, 'paths' => $top];
    }

    // --- funnel --------------------------------------------------------------

    /**
     * A funnel of any steps, in order: `screen:<node>`, `tap:<id>`,
     * `event:<name>` or `event:<name>:<prop>=<value>`.
     *
     * Counted per install over the whole range, or per session. A step counts
     * only after the one before it — reaching the paywall and then onboarding
     * is not a pass through "onboarding → paywall". The time between steps is
     * the median over the units that made it.
     *
     * @param  list<string>  $specs
     * @return array{unit: string, units: int, steps: list<array>}
     */
    public function funnel(AnalyticsRange $range, array $specs, string $unit = 'install'): array
    {
        $steps = array_values(array_filter(array_map(fn ($spec) => self::parseStep((string) $spec), $specs)));
        $unit = $unit === 'session' ? 'session' : 'install';
        if ($steps === []) {
            return ['unit' => $unit, 'units' => 0, 'steps' => []];
        }

        $names = [];
        foreach ($steps as $step) {
            array_push($names, ...($step['type'] === 'screen' ? ['screen', 'onboarding_step'] : [$step['name']]));
        }

        $key = $unit === 'session' ? 'session_id' : 'install_id';
        $rows = $range->events()
            ->whereIn('name', array_unique($names))
            ->when($unit === 'session', fn ($q) => $q->whereNotNull('session_id'))
            ->select(['id', 'install_id', 'session_id', 'name', 'screen', 'props', 'occurred_at'])
            ->orderBy($key)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->toBase()
            ->cursor();

        $reached = array_fill(0, count($steps), 0);
        $gaps = array_fill(0, count($steps), []);
        $units = 0;
        $current = null;
        $pointer = 0;
        $lastAt = null;

        foreach ($rows as $row) {
            if ($row->{$key} !== $current) {
                $current = $row->{$key};
                $pointer = 0;
                $lastAt = null;
                $units++;
            }
            if ($pointer >= count($steps)) {
                continue;
            }

            $props = null;
            $step = $steps[$pointer];
            if (! $this->matches($step, $row, $props)) {
                continue;
            }

            $at = self::ms((string) $row->occurred_at);
            $reached[$pointer]++;
            if ($lastAt !== null && count($gaps[$pointer]) < self::DWELL_SAMPLES) {
                $gaps[$pointer][] = $at - $lastAt;
            }
            $lastAt = $at;
            $pointer++;
        }

        // Everyone who could have started counts as the population, not only
        // those who produced one of the funnel's events.
        $population = $unit === 'session'
            ? $range->events()->whereNotNull('session_id')->distinct()->count('session_id')
            : $range->events()->distinct()->count('install_id');

        $result = [];
        foreach ($steps as $i => $step) {
            sort($gaps[$i]);
            $result[] = [
                'spec' => $step['spec'],
                'label' => $step['label'],
                'reached' => $reached[$i],
                'ofStart' => self::rate($reached[$i], $population),
                'ofPrevious' => $i === 0 ? null : self::rate($reached[$i], $reached[$i - 1]),
                'medianSeconds' => $gaps[$i] !== [] ? round($gaps[$i][intdiv(count($gaps[$i]), 2)] / 1000) : null,
            ];
        }

        return ['unit' => $unit, 'units' => $population, 'steps' => $result];
    }

    /** `screen:x`, `tap:x`, `event:x`, `event:x:key=value` → a matcher, or null. */
    public static function parseStep(string $spec): ?array
    {
        $spec = trim($spec);
        if (! preg_match('/^(screen|tap|event):(.+)$/', $spec, $m)) {
            return null;
        }
        [$type, $rest] = [$m[1], $m[2]];

        if ($type === 'screen') {
            return ['spec' => $spec, 'type' => 'screen', 'node' => $rest, 'name' => 'screen', 'label' => $rest];
        }
        if ($type === 'tap') {
            return ['spec' => $spec, 'type' => 'tap', 'name' => 'tap', 'key' => 'id', 'value' => $rest, 'label' => 'Tap '.$rest];
        }

        $parts = explode(':', $rest, 2);
        if (! preg_match(TelemetryIngestor::NAME_PATTERN, $parts[0])) {
            return null;
        }
        $filter = isset($parts[1]) && str_contains($parts[1], '=') ? explode('=', $parts[1], 2) : null;

        return [
            'spec' => $spec,
            'type' => 'event',
            'name' => $parts[0],
            'key' => $filter[0] ?? null,
            'value' => $filter[1] ?? null,
            'label' => $parts[0].($filter ? " ({$filter[0]} = {$filter[1]})" : ''),
        ];
    }

    private function matches(array $step, object $row, ?array &$props): bool
    {
        if ($step['type'] === 'screen') {
            if ($row->name === 'screen') {
                return ($row->screen ?? null) === $step['node']
                    || (($props ??= json_decode($row->props, true) ?: [])['name'] ?? null) === $step['node'];
            }
            if ($row->name === 'onboarding_step') {
                return 'onboarding/'.(($props ??= json_decode($row->props, true) ?: [])['beat'] ?? '') === $step['node'];
            }

            return false;
        }

        if ($row->name !== $step['name']) {
            return false;
        }
        if ($step['key'] === null) {
            return true;
        }

        $value = ($props ??= json_decode($row->props, true) ?: [])[$step['key']] ?? null;
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return is_scalar($value) && (string) $value === $step['value'];
    }
}
