<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Laravel registers api routes BEFORE web routes, so anything declared in
 * routes/api.php at a URI that web.php also uses silently shadows it. The
 * Livewire practice pages depend on POST /api/practice/check-answer keeping
 * its session-backed web middleware, so guard that here.
 */
class RouteCollisionTest extends TestCase
{
    public function test_legacy_practice_check_answer_stays_on_the_web_router(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($r) => $r->uri() === 'api/practice/check-answer' && in_array('POST', $r->methods(), true));

        $this->assertNotNull($route, 'The legacy check-answer route disappeared.');
        $this->assertSame('App\Http\Controllers\PracticeController@checkAnswer', $route->getActionName());
        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_legacy_learning_path_check_answer_stays_on_the_web_router(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($r) => $r->uri() === 'api/learning-path/check-answer' && in_array('POST', $r->methods(), true));

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_no_two_routes_share_a_uri_and_method(): void
    {
        $seen = [];
        $duplicates = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            foreach ($route->methods() as $method) {
                $key = $method.' '.$route->uri();
                if (isset($seen[$key])) {
                    $duplicates[] = $key;
                }
                $seen[$key] = true;
            }
        }

        $this->assertSame([], $duplicates, 'Duplicate route URIs: '.implode(', ', $duplicates));
    }

    public function test_api_v1_requires_authentication(): void
    {
        $this->getJson('/api/v1/me/dashboard')
            ->assertStatus(401);
    }
}
