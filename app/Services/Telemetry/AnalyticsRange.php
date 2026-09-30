<?php

namespace App\Services\Telemetry;

use App\Models\AppEvent;
use App\Models\AppInstall;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The window and platform an analytics page is looking at.
 *
 * Every query in AppAnalytics starts from `events()` or `installs()` here, so
 * a filter added to the page cannot be forgotten by one of its charts.
 */
final class AnalyticsRange
{
    public const PLATFORMS = ['ios', 'android'];

    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ?string $platform = null,
    ) {}

    /** Last 30 days by default; an unknown platform means all of them. */
    public static function fromRequest(Request $request, int $defaultDays = 30): self
    {
        $to = $request->filled('to')
            ? CarbonImmutable::parse($request->date('to'))->endOfDay()
            : CarbonImmutable::now()->endOfDay();
        $from = $request->filled('from')
            ? CarbonImmutable::parse($request->date('from'))->startOfDay()
            : $to->subDays($defaultDays - 1)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $platform = in_array($request->query('platform'), self::PLATFORMS, true)
            ? $request->query('platform')
            : null;

        return new self($from, $to, $platform);
    }

    /** The window of the same length that ends where this one starts, for "vs. previous period". */
    public function previous(): self
    {
        $days = (int) $this->from->diffInDays($this->to->startOfDay()) + 1;

        return new self($this->from->subDays($days), $this->from->subSecond(), $this->platform);
    }

    /** Every day of the window, as `Y-m-d`, so a quiet day is a zero rather than a gap. */
    public function days(): array
    {
        $days = [];
        for ($day = $this->from->startOfDay(); $day->lte($this->to); $day = $day->addDay()) {
            $days[] = $day->toDateString();
        }

        return $days;
    }

    /** Events inside the window, on the platform. */
    public function events(): Builder
    {
        return AppEvent::query()
            ->whereBetween('occurred_at', [$this->from, $this->to])
            ->when($this->platform, fn (Builder $q) => $q->where('platform', $this->platform));
    }

    /** Installs first seen inside the window, on the platform — a cohort of new visitors. */
    public function installs(): Builder
    {
        return AppInstall::query()
            ->whereBetween('first_seen_at', [$this->from, $this->to])
            ->when($this->platform, fn (Builder $q) => $q->where('platform', $this->platform));
    }

    /** A stable key for caching whatever was computed over this range. */
    public function key(string $what): string
    {
        return 'app-analytics:'.$what.':'.$this->from->toDateString().':'.$this->to->toDateString().':'.($this->platform ?? 'all');
    }

    /** The query string that reproduces this range, for links between the pages. */
    public function query(): array
    {
        return array_filter([
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'platform' => $this->platform,
        ]);
    }
}
