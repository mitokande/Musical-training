<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingCacheHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_landing_is_publicly_cacheable_and_varies_by_cookie(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=300', $cacheControl);
        $this->assertStringContainsString('Cookie', (string) $response->headers->get('Vary'));
    }

    public function test_authenticated_landing_is_never_cached(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringNotContainsString('public', $cacheControl);
    }
}
