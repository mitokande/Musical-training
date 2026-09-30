<?php

namespace App\Services\Telemetry;

use App\Models\AppEvent;
use Illuminate\Support\Facades\DB;

/**
 * Every event name the app sends, one at a time: how often, by how many, and
 * split by whatever it carries.
 *
 * The admin's Events pages. Nothing here knows the vocabulary — a new event the
 * app starts sending shows up with its own properties to break down by, the
 * day it lands.
 */
class EventMetrics
{
    /** Properties picked to break an event down by when the page does not say, in order of preference. */
    private const PREFERRED_KEYS = ['id', 'from', 'beat', 'nodeId', 'reason', 'outcome', 'key', 'method', 'kind', 'name'];

    /** What a property name must look like to be put into a JSON path. */
    private const KEY_PATTERN = '/^[A-Za-z0-9_]{1,64}$/';

    /** Columns an event can be broken down by, besides its properties. */
    public const COLUMNS = ['screen', 'platform', 'app_version'];

    private function json(string $key): string
    {
        return DB::connection()->getQueryGrammar()->wrap('props->'.$key);
    }

    private static function rate(int|float $part, int|float $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }

    /**
     * Every event name seen in the range, busiest first, with a daily series
     * for a sparkline and the change against the period before.
     */
    public function catalog(AnalyticsRange $range): array
    {
        $rows = $range->events()
            ->selectRaw('name, COUNT(*) as events, COUNT(DISTINCT install_id) as installs, COUNT(DISTINCT user_id) as users, COUNT(DISTINCT session_id) as sessions')
            ->groupBy('name')
            ->orderByDesc('events')
            ->toBase()
            ->get();

        $previous = $range->previous()->events()
            ->selectRaw('name, COUNT(*) as events')
            ->groupBy('name')
            ->toBase()
            ->pluck('events', 'name');

        $daily = $range->events()
            ->selectRaw('name, DATE(occurred_at) as day, COUNT(*) as events')
            ->groupBy('name', 'day')
            ->toBase()
            ->get()
            ->groupBy('name')
            ->map(fn ($days) => $days->pluck('events', 'day'));

        $days = $range->days();

        return $rows->map(function ($row) use ($previous, $daily, $days) {
            $before = (int) ($previous[$row->name] ?? 0);
            $series = $daily->get($row->name, collect());

            return [
                'name' => $row->name,
                'events' => (int) $row->events,
                'installs' => (int) $row->installs,
                'users' => (int) $row->users,
                'perSession' => $row->sessions > 0 ? round($row->events / $row->sessions, 1) : null,
                'change' => $before > 0 ? round(($row->events - $before) / $before * 100) : null,
                'series' => array_map(fn ($day) => (int) ($series[$day] ?? 0), $days),
            ];
        })->all();
    }

    /**
     * One event: totals, a daily series, the properties it carries, and a
     * breakdown by one of them (or by screen, platform or app version).
     */
    public function detail(AnalyticsRange $range, string $name, ?string $by = null): array
    {
        $base = fn () => $range->events()->where('name', $name);

        $totals = $base()
            ->selectRaw('COUNT(*) as events, COUNT(DISTINCT install_id) as installs, COUNT(DISTINCT user_id) as users, COUNT(DISTINCT session_id) as sessions')
            ->toBase()
            ->first();

        $daily = $base()
            ->selectRaw('DATE(occurred_at) as day, COUNT(*) as events, COUNT(DISTINCT install_id) as installs')
            ->groupBy('day')
            ->toBase()
            ->get()
            ->keyBy('day');

        $keys = $this->keys($base()->orderByDesc('occurred_at')->limit(300)->pluck('props'));

        if (! in_array($by, [...$keys, ...self::COLUMNS], true)) {
            $by = collect(self::PREFERRED_KEYS)->first(fn ($key) => in_array($key, $keys, true)) ?? 'screen';
        }

        return [
            'name' => $name,
            'totals' => [
                'events' => (int) ($totals->events ?? 0),
                'installs' => (int) ($totals->installs ?? 0),
                'users' => (int) ($totals->users ?? 0),
                'sessions' => (int) ($totals->sessions ?? 0),
            ],
            'daily' => array_map(fn ($day) => [
                'day' => $day,
                'events' => (int) ($daily[$day]->events ?? 0),
                'installs' => (int) ($daily[$day]->installs ?? 0),
            ], $range->days()),
            'keys' => $keys,
            'by' => $by,
            'breakdown' => $this->breakdown($range, $name, $by, (int) ($totals->events ?? 0)),
            'screens' => $by === 'screen' ? [] : $this->breakdown($range, $name, 'screen', (int) ($totals->events ?? 0), 15),
        ];
    }

    /** The newest occurrences of an event, with who produced them. */
    public function recent(AnalyticsRange $range, string $name, int $limit = 50)
    {
        return $range->events()
            ->where('name', $name)
            ->with('user:id,name,email')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** Scalar property names seen on a sample of the event, most common first. */
    private function keys($sample): array
    {
        $seen = [];
        foreach ($sample as $props) {
            $props = is_string($props) ? json_decode($props, true) : $props;
            foreach ((array) $props as $key => $value) {
                if (is_string($key) && preg_match(self::KEY_PATTERN, $key) && ! is_array($value)) {
                    $seen[$key] = ($seen[$key] ?? 0) + 1;
                }
            }
        }
        arsort($seen);

        return array_keys($seen);
    }

    private function breakdown(AnalyticsRange $range, string $name, string $by, int $total, int $limit = 50): array
    {
        $expression = in_array($by, self::COLUMNS, true) ? $by : $this->json($by);

        return $range->events()
            ->where('name', $name)
            ->selectRaw("$expression as value, COUNT(*) as events, COUNT(DISTINCT install_id) as installs")
            ->groupBy('value')
            ->orderByDesc('events')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'value' => $row->value,
                'events' => (int) $row->events,
                'installs' => (int) $row->installs,
                'share' => self::rate((int) $row->events, $total),
            ])
            ->all();
    }

    /** Whether any event of this name was ever received — a page for a name nobody sends is a 404. */
    public function exists(string $name): bool
    {
        return preg_match(TelemetryIngestor::NAME_PATTERN, $name) === 1
            && AppEvent::where('name', $name)->exists();
    }
}
