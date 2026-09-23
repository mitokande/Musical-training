<?php

namespace Tests\Feature;

use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use App\Services\Telemetry\AnalyticsRange;
use App\Services\Telemetry\AppAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin → Mobile App: the numbers AppAnalytics derives from `app_events`, and
 * the pages that show them.
 *
 * Each test builds a small, fully known population of installs and asserts the
 * figure a person would work out by hand. The JSON booleans (`firstRun`,
 * `correct`) are deliberately real booleans, because that is where SQLite and
 * MySQL disagree and where a portable query is easiest to get wrong.
 */
class AppAnalyticsAdminTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    private AppAnalytics $analytics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-09-23 12:00:00');
        $this->travelTo($this->now);
        $this->analytics = app(AppAnalytics::class);
    }

    private function install(string $id, CarbonImmutable $firstSeen, ?User $user = null, string $platform = 'ios'): string
    {
        AppInstall::create([
            'install_id' => $id,
            'user_id' => $user?->id,
            'platform' => $platform,
            'app_version' => '1.0.0',
            'first_seen_at' => $firstSeen,
            'last_seen_at' => $firstSeen,
        ]);

        return $id;
    }

    private function event(string $install, string $name, array $props, CarbonImmutable $at, ?User $user = null, ?string $session = null): void
    {
        AppEvent::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user?->id,
            'install_id' => $install,
            'session_id' => $session ?? $install,
            'name' => $name,
            'props' => $props,
            'platform' => AppInstall::where('install_id', $install)->value('platform') ?? 'ios',
            'app_version' => '1.0.0',
            'occurred_at' => $at,
            'received_at' => $at,
        ]);
    }

    private function range(int $days = 30, ?string $platform = null): AnalyticsRange
    {
        return new AnalyticsRange($this->now->subDays($days - 1)->startOfDay(), $this->now->endOfDay(), $platform);
    }

    private function beat(string $install, string $beat, int $index, CarbonImmutable $at, bool $firstRun = true): void
    {
        $this->event($install, 'onboarding_step', [
            'beat' => $beat, 'index' => $index, 'total' => 9, 'direction' => 'forward', 'firstRun' => $firstRun,
        ], $at);
    }

    public function test_the_onboarding_funnel_follows_a_cohort_of_new_installs(): void
    {
        $day = $this->now->subDays(2);
        $learner = User::factory()->create();

        // A: all the way to a finished lesson and a purchase.
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $day);
        $this->beat($a, 'hello', 0, $day);
        $this->beat($a, 'q-0', 1, $day->addMinute());
        $this->event($a, 'onboarding_complete', ['firstRun' => true], $day->addMinutes(3));
        $this->event($a, 'auth_success', ['method' => 'google', 'created' => true], $day->addMinutes(4), $learner);
        $this->event($a, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'lesson', 'attempt' => 0], $day->addMinutes(5), $learner);
        $this->event($a, 'lesson_end', ['nodeId' => 'u1-l1', 'reason' => 'finished', 'score' => 100], $day->addMinutes(9), $learner);
        $this->event($a, 'purchase_result', ['from' => 'onboarding', 'offerId' => 'annual', 'outcome' => 'unlocked', 'error' => null], $day->addMinutes(2));

        // B: gone after the first question. C: gone at the greeting.
        $b = $this->install('bbbbbbbb-0000-4000-8000-000000000002', $day);
        $this->beat($b, 'hello', 0, $day);
        $this->beat($b, 'q-0', 1, $day->addMinute());
        $c = $this->install('cccccccc-0000-4000-8000-000000000003', $day);
        $this->beat($c, 'hello', 0, $day);

        // A re-take from the profile tab, by A — not a new visitor's step.
        $this->beat($a, 'plan', 7, $day->addDay(), firstRun: false);

        // An install from before the window: not in the cohort, whatever it does now.
        $old = $this->install('dddddddd-0000-4000-8000-000000000004', $this->now->subDays(60));
        $this->beat($old, 'hello', 0, $day);

        $funnel = $this->analytics->onboardingFunnel($this->range());
        $steps = collect($funnel['steps'])->pluck('installs', 'label');

        $this->assertSame(3, $funnel['cohort']);
        $this->assertSame(3, $steps['Opened the app']);
        $this->assertSame(3, $steps['Onboarding: hello']);
        $this->assertSame(2, $steps['Onboarding: q-0']);
        $this->assertFalse($steps->has('Onboarding: plan'));
        $this->assertSame(1, $steps['Finished onboarding']);
        $this->assertSame(1, $steps['Signed in / created account']);
        $this->assertSame(1, $steps['Finished a lesson']);
        $this->assertSame(1, $steps['Bought Premium']);

        // Beats come out in flow order, not alphabetical.
        $labels = array_column($funnel['steps'], 'label');
        $this->assertLessThan(array_search('Onboarding: q-0', $labels), array_search('Onboarding: hello', $labels));

        $q0 = collect($funnel['steps'])->firstWhere('label', 'Onboarding: q-0');
        $this->assertEqualsWithDelta(66.7, $q0['ofPrevious'], 0.01);
    }

    public function test_the_paywall_funnel_is_split_by_door(): void
    {
        $at = $this->now->subDay();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $at);
        $b = $this->install('bbbbbbbb-0000-4000-8000-000000000002', $at);
        $c = $this->install('cccccccc-0000-4000-8000-000000000003', $at);

        $this->event($a, 'paywall_view', ['from' => 'onboarding', 'wall' => null, 'state' => 'ready', 'offers' => 2], $at);
        $this->event($a, 'purchase_start', ['from' => 'onboarding', 'offerId' => 'annual'], $at);
        $this->event($a, 'purchase_result', ['from' => 'onboarding', 'offerId' => 'annual', 'outcome' => 'unlocked', 'error' => null], $at);
        $this->event($b, 'paywall_view', ['from' => 'onboarding', 'wall' => null, 'state' => 'ready', 'offers' => 2], $at);
        $this->event($b, 'paywall_view', ['from' => 'onboarding', 'wall' => null, 'state' => 'ready', 'offers' => 2], $at);
        $this->event($b, 'paywall_dismiss', ['from' => 'onboarding', 'via' => 'keep_free'], $at);
        $this->event($c, 'paywall_view', ['from' => 'limit', 'wall' => 'quota', 'state' => 'unavailable', 'offers' => 0], $at);

        $doors = $this->analytics->paywallFunnel($this->range())->keyBy('door');

        $this->assertSame(2, $doors['onboarding']['shown']);
        $this->assertSame(1, $doors['onboarding']['unlocked']);
        $this->assertSame(1, $doors['onboarding']['dismissed']);
        $this->assertEquals(50.0, $doors['onboarding']['conversion']);
        $this->assertSame(0, $doors['limit']['shown']);
        $this->assertSame(1, $doors['limit']['unavailable']);
        $this->assertNull($doors['limit']['conversion']);
    }

    public function test_retention_counts_returns_by_day_and_by_week(): void
    {
        $first = $this->now->subDays(10)->setTime(9, 0);
        $keeper = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $first);
        $leaver = $this->install('bbbbbbbb-0000-4000-8000-000000000002', $first);

        foreach ([0, 1, 7] as $day) {
            $this->event($keeper, 'app_open', ['cold' => true], $first->addDays($day));
        }
        $this->event($leaver, 'app_open', ['cold' => true], $first);

        $retention = $this->analytics->retention($this->range(84));

        $this->assertSame(['eligible' => 2, 'returned' => 1, 'rate' => 50.0], $retention['days'][1]);
        $this->assertSame(['eligible' => 2, 'returned' => 1, 'rate' => 50.0], $retention['days'][7]);
        // Ten days old: nobody has had a day 30 yet.
        $this->assertSame(0, $retention['days'][30]['eligible']);

        $cohort = $retention['cohorts'][0];
        $this->assertSame(2, $cohort['size']);
        $this->assertEquals(100.0, $cohort['rates'][0]);
        $this->assertEquals(50.0, $cohort['rates'][1]);
        // A week that has not happened yet for this cohort is blank, not zero.
        $this->assertNull($cohort['rates'][8]);
    }

    public function test_lessons_report_completion_and_where_people_quit(): void
    {
        $at = $this->now->subDay();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $at);

        $this->event($a, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'lesson', 'attempt' => 0], $at);
        $this->event($a, 'lesson_end', ['nodeId' => 'u1-l1', 'reason' => 'abandoned', 'score' => 0, 'seconds' => 40, 'step' => 3, 'steps' => 10], $at);
        $this->event($a, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'lesson', 'attempt' => 1], $at);
        $this->event($a, 'lesson_end', ['nodeId' => 'u1-l1', 'reason' => 'finished', 'score' => 80, 'seconds' => 300, 'step' => 9, 'steps' => 10], $at);
        $this->event($a, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'review', 'attempt' => 2], $at);

        $lesson = $this->analytics->lessons($this->range())->firstWhere('node', 'u1-l1');

        $this->assertSame(2, $lesson['started']);
        $this->assertSame(1, $lesson['reviews']);
        $this->assertSame(1, $lesson['finished']);
        $this->assertEquals(50.0, $lesson['completion']);
        $this->assertEquals(80, $lesson['avgScore']);
        $this->assertEquals(5.0, $lesson['avgMinutes']);
        $this->assertEquals(30, $lesson['quitAt']);
    }

    public function test_intervals_report_first_try_accuracy_and_the_confusion(): void
    {
        $at = $this->now->subDay();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $at);
        $answer = fn (string $chosen, int $attempt = 1, string $kind = 'identify', ?bool $correct = null) => $this->event($a, 'task_answered', [
            'nodeId' => 'u1-l1', 'conceptId' => 'P5', 'kind' => $kind, 'correct' => $correct ?? $chosen === 'P5',
            'chosen' => $chosen, 'attemptNo' => $attempt, 'elapsedMs' => 2000, 'replays' => 1,
        ], $at);

        $answer('P5');
        $answer('P5');
        $answer('P5');
        $answer('P4');
        // A second try is not a first impression.
        $answer('P4', attempt: 2);
        // Building one is a separate skill, answered with a note.
        $answer('G4', kind: 'build', correct: true);
        $answer('F4', kind: 'build', correct: false);

        $concepts = $this->analytics->concepts($this->range());
        $p5 = $concepts['rows']->firstWhere('concept', 'P5');

        $this->assertSame(4, $p5['answers']);
        $this->assertEquals(75.0, $p5['accuracy']);
        $this->assertSame(['chosen' => 'P4', 'share' => 25.0], $p5['topConfusion']);
        $this->assertEquals(1.0, $p5['avgReplays']);
        $this->assertEquals(2.0, $p5['avgSeconds']);
        $this->assertSame(2, $p5['builds']);
        $this->assertEquals(50.0, $p5['buildAccuracy']);

        $this->assertEquals(75.0, $concepts['matrix']['P5']['P5']);
        $this->assertEquals(25.0, $concepts['matrix']['P5']['P4']);
    }

    public function test_the_overview_counts_active_installs_sessions_and_screen_time(): void
    {
        $user = User::factory()->create();
        $today = $this->now->subHour();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $today, $user);
        $b = $this->install('bbbbbbbb-0000-4000-8000-000000000002', $this->now->subDays(5), platform: 'android');

        $this->event($a, 'screen', ['name' => '(tabs)', 'path' => '/', 'params' => [], 'prev' => null, 'prevMs' => null], $today, $user, 's-a');
        $this->event($a, 'screen', ['name' => 'learn/[lesson]/run', 'path' => '/learn/u1-l1/run', 'params' => [], 'prev' => '(tabs)', 'prevMs' => 4000], $today, $user, 's-a');
        $this->event($a, 'app_background', ['foregroundMs' => 120000], $today, $user, 's-a');
        $this->event($b, 'screen', ['name' => '(tabs)', 'path' => '/', 'params' => [], 'prev' => null, 'prevMs' => null], $this->now->subDays(5), session: 's-b');
        $this->event($b, 'app_background', ['foregroundMs' => 360000], $this->now->subDays(5), session: 's-b');
        $this->event($b, 'api_error', ['method' => 'POST', 'path' => '/sessions/:id/answers', 'status' => 502, 'code' => 'unknown'], $this->now->subDays(5), session: 's-b');

        $overview = $this->analytics->overview($this->range());

        $this->assertSame(1, $overview['active']['day']['installs']);
        $this->assertSame(1, $overview['active']['day']['users']);
        $this->assertSame(2, $overview['active']['week']['installs']);
        $this->assertSame(2, $overview['newInstalls']);
        $this->assertSame(2, $overview['sessions']);
        $this->assertEquals(4.0, $overview['avgSessionMinutes']);

        $tabs = $overview['screens']->firstWhere('screen', '(tabs)');
        $this->assertSame(2, $tabs['views']);
        $this->assertEquals(4.0, $tabs['avgSeconds']);

        $this->assertSame(502, (int) $overview['serverErrors']->first()->status);
        $this->assertCount(30, $overview['daily']);

        $android = $this->analytics->overview($this->range(platform: 'android'));
        $this->assertSame(0, $android['active']['day']['installs']);
        $this->assertSame(1, $android['sessions']);
    }

    public function test_the_pages_are_admin_only(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        foreach (['overview', 'funnels', 'retention', 'learning', 'activity'] as $page) {
            $this->actingAs($member)->get(route('admin.app-analytics.'.$page))->assertForbidden();
        }
    }

    public function test_every_page_renders_with_and_without_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['overview', 'funnels', 'retention', 'learning', 'activity'] as $page) {
            $this->actingAs($admin)->get(route('admin.app-analytics.'.$page))->assertOk();
        }

        $learner = User::factory()->create();
        $at = $this->now->subDay();
        $a = $this->install('aaaaaaaa-0000-4000-8000-000000000001', $at, $learner);
        $this->beat($a, 'hello', 0, $at);
        $this->event($a, 'screen', ['name' => '(tabs)', 'path' => '/', 'params' => [], 'prev' => null, 'prevMs' => null], $at, $learner);
        $this->event($a, 'paywall_view', ['from' => 'settings', 'wall' => null, 'state' => 'ready', 'offers' => 2], $at, $learner);
        $this->event($a, 'lesson_start', ['nodeId' => 'u1-l1', 'kind' => 'lesson', 'attempt' => 0], $at, $learner);
        $this->event($a, 'task_answered', [
            'nodeId' => 'u1-l1', 'conceptId' => 'P5', 'kind' => 'identify', 'correct' => false,
            'chosen' => 'P4', 'attemptNo' => 1, 'elapsedMs' => 1500, 'replays' => 0,
        ], $at, $learner);

        // The empty pages above were cached; this is the data arriving later.
        Cache::flush();

        $this->actingAs($admin)->get(route('admin.app-analytics.overview'))->assertOk()->assertSee('(tabs)');
        $this->actingAs($admin)->get(route('admin.app-analytics.funnels'))->assertOk()->assertSee('settings');
        $this->actingAs($admin)->get(route('admin.app-analytics.retention'))->assertOk();
        $this->actingAs($admin)->get(route('admin.app-analytics.learning'))->assertOk()->assertSee('Confusion matrix');
        $this->actingAs($admin)->get(route('admin.app-analytics.activity', ['q' => $learner->email]))
            ->assertOk()
            ->assertSee($learner->name)
            ->assertSee('task_answered');
        $this->actingAs($admin)->get(route('admin.app-analytics.activity', ['q' => $a]))
            ->assertOk()
            ->assertSee('Last signed in as');
        $this->actingAs($admin)->get(route('admin.app-analytics.activity', ['q' => 'nobody@example.com']))
            ->assertOk()
            ->assertSee('Nobody found');
    }

    public function test_the_member_page_links_to_their_app_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee(route('admin.app-analytics.activity', ['q' => $member->id]), false);
    }
}
