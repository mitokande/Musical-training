<?php

namespace Tests\Feature;

use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use App\Services\Telemetry\AnalyticsRange;
use App\Services\Telemetry\EventMetrics;
use App\Services\Telemetry\JourneyAnalytics;
use App\Services\Telemetry\UserJourneys;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Journeys, screens, taps and events: the pages that read `app_events` in
 * order, one session at a time.
 *
 * Each test scripts a few sessions second by second, the way the app would
 * have sent them, and asserts what a person replaying them by hand would say:
 * where they went, how long each screen was really up, who got how far.
 */
class AppJourneysTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    private JourneyAnalytics $journeys;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-09-30 12:00:00');
        $this->travelTo($this->now);
        $this->journeys = app(JourneyAnalytics::class);
    }

    private function range(): AnalyticsRange
    {
        return new AnalyticsRange($this->now->subDays(6)->startOfDay(), $this->now->endOfDay());
    }

    private function install(string $id, ?User $user = null): string
    {
        AppInstall::firstOrCreate(['install_id' => $id], [
            'user_id' => $user?->id,
            'platform' => 'ios',
            'app_version' => '1.1.0',
            'first_seen_at' => $this->now->subDays(2),
            'last_seen_at' => $this->now->subHour(),
        ]);

        return $id;
    }

    /**
     * A session as a script: `[seconds, name, props, screen]` rows, the
     * screen stamped the way the app stamps it.
     */
    private function sitting(string $install, array $script, ?User $user = null, ?CarbonImmutable $start = null): string
    {
        $session = (string) Str::uuid();
        $start ??= $this->now->subDay();

        foreach ($script as [$second, $name, $props, $screen]) {
            AppEvent::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user?->id,
                'install_id' => $install,
                'session_id' => $session,
                'screen' => $screen,
                'name' => $name,
                'props' => $props,
                'platform' => 'ios',
                'app_version' => '1.1.0',
                'occurred_at' => $start->addSeconds($second),
                'received_at' => $start->addSeconds($second),
            ]);
        }

        return $session;
    }

    private static function screen(int $at, string $name): array
    {
        return [$at, 'screen', ['name' => $name, 'path' => '/'.$name, 'params' => [], 'prev' => null, 'prevMs' => null], $name];
    }

    private static function tap(int $at, string $screen, ?string $id): array
    {
        return [$at, 'tap', ['id' => $id, 'kind' => 'press'], $screen];
    }

    // --- the fold ------------------------------------------------------------

    public function test_a_session_folds_into_steps_with_visible_time_only(): void
    {
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $this->sitting($a, [
            [0, 'app_open', ['cold' => true, 'newSession' => true, 'backgroundMs' => null], null],
            self::screen(0, '(tabs)/index'),
            self::tap(5, '(tabs)/index', 'home:start'),
            self::screen(10, 'learn/[lesson]/run'),
            // Away for five minutes mid-lesson: none of it is time on the screen.
            [40, 'app_background', ['foregroundMs' => 40000, 'screenMs' => 30000], 'learn/[lesson]/run'],
            [340, 'app_open', ['cold' => false, 'newSession' => false, 'backgroundMs' => 300000], 'learn/[lesson]/run'],
            self::screen(350, 'learn/[lesson]/complete'),
            self::screen(351, 'learn/[lesson]/complete'),
            [360, 'app_background', ['foregroundMs' => 20000, 'screenMs' => 10000], 'learn/[lesson]/complete'],
        ]);

        $sessions = [];
        $this->journeys->eachSession($this->range(), false, function ($install, $steps) use (&$sessions) {
            $sessions[] = $steps;
        });

        $this->assertCount(1, $sessions);
        $this->assertSame(['(tabs)/index', 'learn/[lesson]/run', 'learn/[lesson]/complete'], array_column($sessions[0], 'node'));
        // 30s before leaving + 10s after coming back; the five minutes away are not in it.
        $this->assertSame([10_000, 40_000, 10_000], array_column($sessions[0], 'ms'));
    }

    public function test_onboarding_is_read_beat_by_beat_and_a_killed_session_leaves_its_last_step_untimed(): void
    {
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $this->sitting($a, [
            // The first beat is tracked before the route's screen event, and
            // stamped with whatever was on screen before it.
            [0, 'onboarding_step', ['beat' => 'welcome', 'index' => 0, 'total' => 5, 'direction' => 'forward', 'firstRun' => true], null],
            [0, 'screen', ['name' => 'onboarding', 'path' => '/onboarding', 'params' => [], 'prev' => null, 'prevMs' => null], 'onboarding/welcome'],
            self::tap(4, 'onboarding/welcome', 'onboarding:intro.cta'),
            [4, 'onboarding_step', ['beat' => 'goals', 'index' => 1, 'total' => 5, 'direction' => 'forward', 'firstRun' => true], 'onboarding/goals'],
        ]);

        $sessions = [];
        $this->journeys->eachSession($this->range(), false, function ($install, $steps) use (&$sessions) {
            $sessions[] = $steps;
        });

        $this->assertSame(['onboarding/welcome', 'onboarding/goals'], array_column($sessions[0], 'node'));
        $this->assertSame([4_000, null], array_column($sessions[0], 'ms'));
    }

    // --- screens -------------------------------------------------------------

    public function test_screens_count_views_entries_exits_and_where_people_go_next(): void
    {
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $b = $this->install('bbbbbbbb-0000-4000-8000-000000000002');

        $this->sitting($a, [self::screen(0, 'home'), self::screen(10, 'paywall'), self::screen(20, 'home'), [30, 'app_background', ['foregroundMs' => 30000], 'home']]);
        $this->sitting($b, [self::screen(0, 'home'), self::tap(2, 'home', 'home:start'), self::tap(3, 'home', 'home:start'), self::screen(4, 'lesson')]);

        $result = $this->journeys->screens($this->range());
        $home = $result['screens']['home'];

        $this->assertSame(2, $result['sessions']);
        $this->assertSame(3, $home['views']);
        $this->assertSame(2, $home['viewers']);
        $this->assertSame(2, $home['entries']);
        $this->assertSame(1, $home['exits']);
        $this->assertSame(2, $home['taps']);
        $this->assertSame(1, $home['tappers']);
        $this->assertEquals(['paywall' => 1, 'lesson' => 1, JourneyAnalytics::EXIT => 1], $result['transitions']['home']);
        // Home was up 10s, 10s and 4s: the median is 10.
        $this->assertSame(10.0, $home['medianSeconds']);

        $taps = $this->journeys->taps($this->range(), 'home', ['home' => 2]);
        $this->assertSame([['screen' => 'home', 'control' => 'home:start', 'taps' => 2, 'tappers' => 1, 'reach' => 50.0]], $taps);
    }

    // --- flows ---------------------------------------------------------------

    public function test_a_flow_follows_sessions_from_a_step_and_folds_the_quiet_ones(): void
    {
        $x = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $this->sitting($x, [self::screen(0, 'home'), self::screen(5, 'paywall'), self::screen(9, 'home')]);
        $this->sitting($x, [self::screen(0, 'home'), self::screen(5, 'paywall')]);
        $this->sitting($x, [self::screen(0, 'library'), self::screen(3, 'home'), self::screen(5, 'lesson')]);

        $flow = $this->journeys->flow($this->range(), 'home', 'after', depth: 2, width: 1);

        $this->assertSame(3, $flow['sessions']);
        $this->assertSame(3, $flow['matched']);

        $links = collect($flow['links'])->mapWithKeys(fn ($link) => [$link['source'].' > '.$link['target'] => $link['value']])->all();
        // After home, paywall twice; lesson once, folded into (other) at width 1.
        $this->assertSame(2, $links['0|home > 1|paywall']);
        $this->assertSame(1, $links['0|home > 1|(other)']);
        $this->assertSame(1, $links['1|paywall > 2|(exit)']);
        $this->assertSame(1, $links['1|paywall > 2|home']);

        $before = $this->journeys->flow($this->range(), 'paywall', 'before', depth: 2);
        $this->assertSame(2, $before['matched']);
        $this->assertContains(['source' => '1|(start)', 'target' => '2|home', 'value' => 2], $before['links']);
        $this->assertContains(['source' => '2|home', 'target' => '3|paywall', 'value' => 2], $before['links']);
    }

    public function test_milestones_join_the_flow_only_when_asked_and_only_when_they_happened(): void
    {
        $x = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $this->sitting($x, [
            self::screen(0, 'paywall'),
            [3, 'purchase_result', ['from' => 'onboarding', 'offerId' => 'y', 'outcome' => 'cancelled', 'error' => null], 'paywall'],
            [6, 'purchase_result', ['from' => 'onboarding', 'offerId' => 'y', 'outcome' => 'unlocked', 'error' => null], 'paywall'],
            self::screen(8, 'home'),
        ]);

        $plain = $this->journeys->topPaths($this->range());
        $this->assertSame(['paywall', 'home', JourneyAnalytics::EXIT], $plain['paths'][0]['steps']);

        $story = $this->journeys->topPaths($this->range(), milestones: true);
        $this->assertSame(['paywall', '★ Bought Premium', 'home', JourneyAnalytics::EXIT], $story['paths'][0]['steps']);
    }

    // --- funnel --------------------------------------------------------------

    public function test_a_funnel_counts_steps_in_order_only(): void
    {
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $b = $this->install('bbbbbbbb-0000-4000-8000-000000000002');
        $c = $this->install('cccccccc-0000-4000-8000-000000000003');

        // a: paywall, then buys.
        $this->sitting($a, [self::screen(0, 'paywall'), self::tap(4, 'paywall', 'paywall:cta'), [10, 'purchase_result', ['outcome' => 'unlocked'], 'paywall']]);
        // b: bought first, saw the paywall after — not a pass through the funnel.
        $this->sitting($b, [[0, 'purchase_result', ['outcome' => 'unlocked'], null], self::screen(5, 'paywall')]);
        // c: paywall, cancelled.
        $this->sitting($c, [self::screen(0, 'paywall'), [3, 'purchase_result', ['outcome' => 'cancelled'], 'paywall']]);

        $funnel = $this->journeys->funnel($this->range(), [
            'screen:paywall',
            'tap:paywall:cta',
            'event:purchase_result:outcome=unlocked',
            'nonsense',
        ]);

        $this->assertSame(3, $funnel['units']);
        $this->assertSame([3, 1, 1], array_column($funnel['steps'], 'reached'));
        $this->assertSame([100.0, 33.3, 33.3], array_column($funnel['steps'], 'ofStart'));
        $this->assertSame([null, 33.3, 100.0], array_column($funnel['steps'], 'ofPrevious'));
        $this->assertSame([4.0, 6.0], [$funnel['steps'][1]['medianSeconds'], $funnel['steps'][2]['medianSeconds']]);

        // Per session asks for it all in one sitting: a's sitting still counts.
        $perSession = $this->journeys->funnel($this->range(), ['screen:paywall', 'event:purchase_result:outcome=unlocked'], 'session');
        $this->assertSame([3, 1], array_column($perSession['steps'], 'reached'));
    }

    // --- one learner ---------------------------------------------------------

    public function test_a_timeline_groups_events_under_the_screen_they_happened_on(): void
    {
        $learner = User::factory()->create();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $learner);
        $session = $this->sitting($a, [
            [0, 'app_open', ['cold' => true, 'newSession' => true, 'backgroundMs' => null], null],
            self::screen(1, 'home'),
            self::tap(3, 'home', 'home:start'),
            self::screen(4, 'lesson'),
            [6, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'lesson', 'attempt' => 1], 'lesson'],
            [10, 'app_background', ['foregroundMs' => 10000, 'screenMs' => 6000], 'lesson'],
        ], $learner);

        $users = app(UserJourneys::class);
        $timeline = $users->timelines($learner, null, [$session])[$session];

        $this->assertSame(['home', 'lesson'], $timeline['path']);
        [$home, $lesson] = $timeline['visits'];
        // The launch opens the first screen's list rather than a visit of its own.
        $this->assertSame(['app_open', 'tap'], array_column($home['items'], 'name'));
        $this->assertSame('home:start', $home['items'][1]['title']);
        $this->assertSame(3000, $home['ms']);
        $this->assertSame(['lesson_start', 'app_background'], array_column($lesson['items'], 'name'));
        $this->assertSame(6000, $lesson['ms']);

        $sessions = $users->sessions($learner, null);
        $this->assertSame(1, (int) $sessions->first()->taps);
        $this->assertSame(10000, (int) $sessions->first()->foreground_ms);
    }

    public function test_the_users_list_filters_by_segment(): void
    {
        $buyer = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $quitter = $this->install('bbbbbbbb-0000-4000-8000-000000000002');
        $this->sitting($buyer, [[0, 'purchase_result', ['outcome' => 'unlocked'], 'paywall']]);
        $this->sitting($quitter, [[0, 'onboarding_step', ['beat' => 'goals', 'index' => 1, 'total' => 5, 'direction' => 'forward', 'firstRun' => true], null]]);

        $users = app(UserJourneys::class);
        $this->assertSame([$buyer], $users->list($this->range(), 'purchased')->pluck('install_id')->all());
        $this->assertSame([$quitter], $users->list($this->range(), 'onboarding_dropout')->pluck('install_id')->all());
        $this->assertSame(2, $users->list($this->range(), 'anonymous')->total());
    }

    // --- events --------------------------------------------------------------

    public function test_an_event_breaks_down_by_its_properties_and_by_screen(): void
    {
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001');
        $this->sitting($a, [self::tap(0, 'home', 'home:start'), self::tap(1, 'home', 'home:start'), self::tap(2, 'profile', null)]);
        // The week before, a single tap: the change is +200%.
        $this->sitting($a, [self::tap(0, 'home', 'home:start')], start: $this->now->subDays(10));

        $metrics = app(EventMetrics::class);

        $tap = collect($metrics->catalog($this->range()))->firstWhere('name', 'tap');
        $this->assertSame(3, $tap['events']);
        $this->assertSame(200.0, (float) $tap['change']);
        $this->assertCount(7, $tap['series']);

        $detail = $metrics->detail($this->range(), 'tap');
        $this->assertSame('id', $detail['by']);
        $this->assertSame(['home:start', null], array_column($detail['breakdown'], 'value'));
        $this->assertSame(['home', 'profile'], array_column($detail['screens'], 'value'));

        $this->assertSame('screen', $metrics->detail($this->range(), 'tap', 'screen')['by']);
        // A key that is not one of the event's own falls back rather than reaching SQL.
        $this->assertSame('id', $metrics->detail($this->range(), 'tap', "id') OR 1=1 --")['by']);
    }
}
