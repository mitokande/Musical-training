<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppEvent;
use App\Services\Telemetry\AnalyticsRange;
use App\Services\Telemetry\AppAnalytics;
use App\Services\Telemetry\EventMetrics;
use App\Services\Telemetry\JourneyAnalytics;
use App\Services\Telemetry\UserJourneys;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Admin → Mobile App: what learners do in the app, from its own telemetry.
 *
 * Two ways in. The aggregate pages — Overview, Flows, Screens, Events,
 * Funnels, Retention, Learning — answer "what do people do", computed live
 * from the event stream and cached for a few minutes: long enough that
 * clicking between tabs is instant, short enough that a release morning is
 * watchable. Users and Journey answer "what did *this* person do", one
 * session, one screen, one tap at a time, and are never cached.
 */
class AppAnalyticsController extends Controller
{
    private const CACHE_SECONDS = 600;

    public function __construct(
        private readonly AppAnalytics $analytics,
        private readonly JourneyAnalytics $journeys,
        private readonly EventMetrics $events,
        private readonly UserJourneys $users,
    ) {}

    private function cached(AnalyticsRange $range, string $what, callable $compute): mixed
    {
        return Cache::remember($range->key($what), self::CACHE_SECONDS, $compute);
    }

    public function overview(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $data = $this->cached($range, 'overview', fn () => $this->analytics->overview($range));
        $topEvents = array_slice($this->cached($range, 'events', fn () => $this->events->catalog($range)), 0, 10);

        return view('admin.app-analytics.overview', ['range' => $range, 'topEvents' => $topEvents] + $data);
    }

    // --- people --------------------------------------------------------------

    /** Installs active in the range, each a way into its journey. */
    public function users(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $segment = array_key_exists($request->query('segment'), UserJourneys::SEGMENTS) ? $request->query('segment') : 'all';
        $q = trim((string) $request->query('q', ''));

        return view('admin.app-analytics.users', [
            'range' => $range,
            'segment' => $segment,
            'q' => $q,
            'installs' => $this->users->list($range, $segment, $q),
        ]);
    }

    /**
     * One learner's journey: who they are, the turns their story took, and
     * every session — newest first — as the screens they saw and what they did
     * on each. `session` opens that session and pages to it.
     */
    public function journey(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        ['user' => $user, 'install' => $install] = $this->users->resolve($q);

        if (! $user && ! $install) {
            return view('admin.app-analytics.journey', ['q' => $q, 'user' => null, 'install' => null]);
        }

        $sessions = $this->users->sessions($user, $install);

        $focus = (string) $request->query('session', '');
        $page = max(1, (int) $request->query('page', 1));
        if ($focus !== '' && ! $request->has('page')) {
            $index = $sessions->search(fn ($session) => $session->session_id === $focus);
            $page = $index === false ? 1 : intdiv($index, UserJourneys::SESSIONS_PER_PAGE) + 1;
        }

        $paginator = new LengthAwarePaginator(
            $sessions->forPage($page, UserJourneys::SESSIONS_PER_PAGE)->values(),
            $sessions->count(),
            UserJourneys::SESSIONS_PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->except('page', 'session')],
        );

        return view('admin.app-analytics.journey', [
            'q' => $q,
            'user' => $user,
            'install' => $install,
            'focus' => $focus,
            'sessions' => $paginator,
            'timelines' => $this->users->timelines($user, $install, $paginator->pluck('session_id')->all()),
        ] + $this->users->summary($user, $install));
    }

    // --- paths ---------------------------------------------------------------

    /**
     * The Sankey of what sessions do next — from their start, or from any
     * screen or milestone, forward or backward — and the openings most
     * sessions share.
     */
    public function flows(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request, defaultDays: 14);
        // `step`, not `from`: `from` is the range's first day on every page.
        $from = trim((string) $request->query('step', '')) ?: null;
        $direction = $request->query('direction') === 'before' ? 'before' : 'after';
        $depth = min(6, max(1, (int) $request->query('depth', 4)));
        $width = min(12, max(3, (int) $request->query('width', 7)));
        $milestones = $request->boolean('milestones', true);

        $options = compact('from', 'direction', 'depth', 'width', 'milestones');
        $flow = $this->cached($range, 'flow:'.md5(json_encode($options)), fn () => $this->journeys->flow($range, $from, $direction, $depth, $width, $milestones));
        $paths = $this->cached($range, 'paths:'.($milestones ? 1 : 0), fn () => $this->journeys->topPaths($range, 5, 25, $milestones));
        $screens = $this->cached($range, 'screens', fn () => $this->journeys->screens($range));

        return view('admin.app-analytics.flows', [
            'range' => $range,
            'options' => $options,
            'flow' => $flow,
            'paths' => $paths,
            'nodes' => array_merge(array_keys($screens['screens']), array_values(JourneyAnalytics::MILESTONES)),
        ]);
    }

    /** Every screen: traffic, time, arrivals and exits, and what gets tapped where. */
    public function screens(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $screens = $this->cached($range, 'screens', fn () => $this->journeys->screens($range));
        $viewers = array_map(fn ($screen) => $screen['viewers'] ?? 0, $screens['screens']);
        $taps = $this->cached($range, 'taps', fn () => $this->journeys->taps($range, null, $viewers, 60));

        return view('admin.app-analytics.screens', ['range' => $range, 'taps' => $taps] + $screens);
    }

    /** One screen: where people come from, where they go, how long they stay, what they touch. */
    public function screen(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $name = (string) $request->query('name', '');
        $screens = $this->cached($range, 'screens', fn () => $this->journeys->screens($range));
        $screen = $screens['screens'][$name] ?? null;

        $incoming = [];
        foreach ($screens['transitions'] as $from => $targets) {
            if (isset($targets[$name])) {
                $incoming[$from] = $targets[$name];
            }
        }
        arsort($incoming);

        $taps = $screen
            ? $this->cached($range, 'taps:'.md5($name), fn () => $this->journeys->taps($range, $name, [$name => $screen['viewers'] ?? 0], 100))
            : [];

        return view('admin.app-analytics.screen', [
            'range' => $range,
            'name' => $name,
            'screen' => $screen,
            'entries' => $screen['entries'] ?? 0,
            'incoming' => array_slice($incoming, 0, 12, true),
            'outgoing' => array_slice($screens['transitions'][$name] ?? [], 0, 12, true),
            'taps' => $taps,
        ]);
    }

    // --- events --------------------------------------------------------------

    public function events(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);

        return view('admin.app-analytics.events', [
            'range' => $range,
            'events' => $this->cached($range, 'events', fn () => $this->events->catalog($range)),
        ]);
    }

    public function event(Request $request, string $name)
    {
        abort_unless($this->events->exists($name), 404);

        $range = AnalyticsRange::fromRequest($request);
        $by = $request->query('by');
        $by = is_string($by) ? $by : null;

        return view('admin.app-analytics.event', [
            'range' => $range,
            'recent' => $this->events->recent($range, $name),
        ] + $this->cached($range, 'event:'.$name.':'.md5((string) $by), fn () => $this->events->detail($range, $name, $by)));
    }

    // --- the fixed pages -----------------------------------------------------

    /**
     * Suggested steps for the custom funnel when none are given: first
     * question to purchase. The paywall comes before `onboarding_complete` in
     * the app — it is pushed over the flow's last beat — so that event is not
     * a step here.
     */
    private const DEFAULT_FUNNEL = [
        'screen:onboarding/welcome',
        'event:paywall_view',
        'event:purchase_start',
        'event:purchase_result:outcome=unlocked',
    ];

    public function funnels(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $onboarding = $this->cached($range, 'onboarding', fn () => $this->analytics->onboardingFunnel($range));
        $paywall = $this->cached($range, 'paywall', fn () => $this->analytics->paywallFunnel($range));

        $specs = array_values(array_filter(array_map('strval', (array) $request->query('steps', self::DEFAULT_FUNNEL)), fn ($s) => trim($s) !== ''));
        $specs = array_slice($specs, 0, 8);
        $unit = $request->query('unit') === 'session' ? 'session' : 'install';
        $custom = $this->cached($range, 'funnel:'.md5(json_encode([$specs, $unit])), fn () => $this->journeys->funnel($range, $specs, $unit));

        // What the step fields offer: every screen and every event seen in the range.
        $screens = $this->cached($range, 'screens', fn () => $this->journeys->screens($range));
        $events = $this->cached($range, 'events', fn () => $this->events->catalog($range));
        $suggestions = array_merge(
            array_map(fn ($node) => 'screen:'.$node, array_keys($screens['screens'])),
            array_map(fn ($event) => 'event:'.$event['name'], $events),
        );

        return view('admin.app-analytics.funnels', compact('range', 'onboarding', 'paywall', 'custom', 'specs', 'unit', 'suggestions'));
    }

    public function retention(Request $request)
    {
        // Cohorts need history to mean anything: twelve weeks unless asked.
        $range = AnalyticsRange::fromRequest($request, defaultDays: 84);
        $retention = $this->cached($range, 'retention', fn () => $this->analytics->retention($range));

        return view('admin.app-analytics.retention', compact('range', 'retention'));
    }

    public function learning(Request $request)
    {
        $range = AnalyticsRange::fromRequest($request);
        $lessons = $this->cached($range, 'lessons', fn () => $this->analytics->lessons($range));
        $concepts = $this->cached($range, 'concepts', fn () => $this->analytics->concepts($range));

        return view('admin.app-analytics.learning', compact('range', 'lessons', 'concepts'));
    }

    /**
     * The live stream: the latest events from everyone, newest first, as they
     * land. One learner's stream is their journey now; an old `?q=` link goes
     * there.
     */
    public function activity(Request $request)
    {
        if (trim((string) $request->query('q', '')) !== '') {
            return redirect()->route('admin.app-analytics.journey', ['q' => $request->query('q')]);
        }

        $name = (string) $request->query('name', '');
        $hideTaps = $request->boolean('hide_taps');

        $events = AppEvent::query()
            ->with('user:id,name,email')
            ->when($name !== '', fn ($query) => $query->where('name', $name))
            ->when($hideTaps && $name === '', fn ($query) => $query->where('name', '!=', 'tap'))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString();

        return view('admin.app-analytics.activity', [
            'name' => $name,
            'hideTaps' => $hideTaps,
            'events' => $events,
            'names' => Cache::remember('app-analytics:names', self::CACHE_SECONDS, fn () => AppEvent::query()
                ->where('occurred_at', '>=', now()->subDays(30))
                ->distinct()
                ->orderBy('name')
                ->pluck('name')),
        ]);
    }
}
