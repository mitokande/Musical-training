<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Analytics\PostHogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PostHogTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'phc_test_key';

    /** Configure a working PostHog setup that never leaves the machine. */
    private function enablePostHog(array $overrides = []): void
    {
        config(array_merge([
            'posthog.key' => self::KEY,
            'posthog.enabled' => true,
            'posthog.consumer' => 'noop',
        ], $overrides));
    }

    protected function tearDown(): void
    {
        unset($_COOKIE['ph_'.self::KEY.'_posthog']);

        parent::tearDown();
    }

    public function test_snippet_is_not_rendered_without_a_key(): void
    {
        config(['posthog.key' => null]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('__posthogSettings', false);
        $response->assertDontSee('posthog.js', false);
    }

    public function test_snippet_is_not_rendered_when_disabled(): void
    {
        $this->enablePostHog(['posthog.enabled' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('__posthogSettings', false);
    }

    public function test_snippet_renders_for_a_guest_with_no_identity(): void
    {
        $this->enablePostHog();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('__posthogSettings', false);
        $response->assertSee(self::KEY, false);
        $response->assertSee('https://eu.i.posthog.com', false);
        // A guest must not carry a user block — that is what keeps posthog.js
        // from identifying anonymous traffic against a stale account.
        $response->assertSee('"user":null', false);
    }

    public function test_snippet_identifies_an_authenticated_user_without_leaking_pii(): void
    {
        $this->enablePostHog();
        $user = User::factory()->create([
            'role' => 'teacher',
            'plan' => 'premium',
            'email' => 'instructor@example.com',
            'name' => 'Ada',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('"id":"'.$user->id.'"', false);
        $response->assertSee('"role":"teacher"', false);
        $response->assertSee('"plan":"premium"', false);
        // Person properties are deliberately limited to segmentation fields.
        $response->assertDontSee('instructor@example.com', false);
    }

    public function test_service_is_disabled_without_a_key(): void
    {
        config(['posthog.key' => null]);

        $this->assertFalse($this->app->make(PostHogService::class)->enabled());
    }

    public function test_service_is_disabled_when_switched_off(): void
    {
        $this->enablePostHog(['posthog.enabled' => false]);

        $this->assertFalse($this->app->make(PostHogService::class)->enabled());
    }

    public function test_capture_is_a_silent_no_op_when_unconfigured(): void
    {
        config(['posthog.key' => null]);
        $posthog = $this->app->make(PostHogService::class);
        $user = User::factory()->create();

        $posthog->capture('test_event', ['a' => 1], $user);
        $posthog->identify($user);
        $posthog->captureException(new RuntimeException('boom'));

        // Nothing to assert beyond "this did not blow up": analytics must never
        // be able to fail a request.
        $this->assertTrue(true);
    }

    public function test_capture_does_not_throw_when_enabled(): void
    {
        $this->enablePostHog();
        $posthog = $this->app->make(PostHogService::class);
        $user = User::factory()->create();

        $posthog->capture('test_event', ['a' => 1], $user);
        $posthog->captureException(new RuntimeException('boom'));

        $this->assertTrue(true);
    }

    /**
     * Swap the container's PostHogService for a recorder so the tests can assert
     * what the exception handler forwarded without touching the network.
     */
    private function spyOnPostHog(): PostHogService
    {
        $spy = new class extends PostHogService
        {
            public array $captured = [];

            public array $events = [];

            public function captureException(\Throwable $e, array $context = []): void
            {
                $this->captured[] = $e;
            }

            public function capture(string $event, array $properties = [], ?User $user = null): void
            {
                $this->events[] = ['event' => $event, 'properties' => $properties, 'user' => $user];
            }
        };

        $this->app->instance(PostHogService::class, $spy);

        return $spy;
    }

    public function test_reported_exceptions_reach_posthog(): void
    {
        $this->enablePostHog();
        $spy = $this->spyOnPostHog();

        $this->app->make(ExceptionHandler::class)->report(new RuntimeException('boom'));

        $this->assertCount(1, $spy->captured);
        $this->assertSame('boom', $spy->captured[0]->getMessage());
    }

    public function test_expected_exceptions_are_not_reported_as_errors(): void
    {
        $this->enablePostHog();
        $spy = $this->spyOnPostHog();
        $handler = $this->app->make(ExceptionHandler::class);

        // Laravel skips reportable callbacks for its "don't report" list, which is
        // what keeps 404s and failed form validation out of error tracking.
        $handler->report(new NotFoundHttpException('missing'));
        $handler->report(ValidationException::withMessages(['email' => 'required']));

        $this->assertSame([], $spy->captured);
    }

    public function test_registration_is_captured_server_side(): void
    {
        $this->enablePostHog();
        $spy = $this->spyOnPostHog();
        $user = User::factory()->create(['role' => 'teacher']);

        event(new Registered($user));

        $this->assertCount(1, $spy->events);
        $this->assertSame('user_registered', $spy->events[0]['event']);
        $this->assertSame('teacher', $spy->events[0]['properties']['role']);
        $this->assertSame('password', $spy->events[0]['properties']['method']);
        $this->assertSame($user->id, $spy->events[0]['user']->id);
    }

    public function test_social_signups_are_distinguished_from_password_signups(): void
    {
        $this->enablePostHog();
        $spy = $this->spyOnPostHog();

        event(new Registered(User::factory()->create(['google_id' => 'g-123'])));

        $this->assertSame('google', $spy->events[0]['properties']['method']);
    }

    public function test_login_is_captured_server_side(): void
    {
        $this->enablePostHog();
        $spy = $this->spyOnPostHog();
        $user = User::factory()->create(['role' => 'user']);

        event(new Login('web', $user, false));

        $this->assertSame('user_logged_in', $spy->events[0]['event']);
    }

    public function test_distinct_id_prefers_the_authenticated_user(): void
    {
        $this->enablePostHog();
        $user = User::factory()->create();

        $this->assertSame(
            (string) $user->id,
            $this->app->make(PostHogService::class)->distinctId($user)
        );
    }

    public function test_distinct_id_falls_back_to_the_posthog_js_cookie(): void
    {
        $this->enablePostHog();
        $_COOKIE['ph_'.self::KEY.'_posthog'] = urlencode(json_encode([
            'distinct_id' => 'device-abc',
            '$device_id' => 'device-abc',
        ]));

        // Reusing posthog-js's own id is what stops one visitor from becoming two
        // separate people across the server and browser SDKs.
        $this->assertSame('device-abc', $this->app->make(PostHogService::class)->distinctId());
    }

    public function test_distinct_id_falls_back_to_anonymous_without_a_cookie(): void
    {
        $this->enablePostHog();

        $this->assertSame('anonymous', $this->app->make(PostHogService::class)->distinctId());
    }

    public function test_malformed_cookie_does_not_break_distinct_id(): void
    {
        $this->enablePostHog();
        $_COOKIE['ph_'.self::KEY.'_posthog'] = 'not-json';

        $this->assertSame('anonymous', $this->app->make(PostHogService::class)->distinctId());
    }
}
