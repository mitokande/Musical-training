<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The `/` landing must serve the English x-default (200) to any visitor without
 * an EXPLICIT non-English language signal — most importantly search-engine and
 * AI crawlers, which send no Accept-Language. A locale redirect there would turn
 * `/` into a "page with redirect" and keep the homepage out of every index.
 */
class LocaleLandingRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_crawler_without_accept_language_gets_english_landing_not_a_redirect(): void
    {
        // No Accept-Language, no session, no auth — the crawler case.
        $this->get('/')->assertOk();
    }

    public function test_english_accept_language_serves_landing_directly(): void
    {
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertOk();
    }

    public function test_explicit_non_english_browser_language_redirects_to_locale_landing(): void
    {
        $this->get('/', ['Accept-Language' => 'de-DE,de;q=0.9'])
            ->assertRedirect('/de');
    }

    public function test_manually_chosen_guest_language_redirects(): void
    {
        $this->withSession(['locale' => 'tr', 'locale_selected' => true])
            ->get('/')
            ->assertRedirect('/tr');
    }

    public function test_ip_seeded_session_locale_does_not_redirect(): void
    {
        // A guest whose session locale came from IP geolocation (no explicit
        // choice) must still get the x-default landing at `/`.
        $this->withSession(['locale' => 'de'])
            ->get('/')
            ->assertOk();
    }

    public function test_authenticated_user_locale_redirects(): void
    {
        $user = User::factory()->create(['locale' => 'fr']);

        $this->actingAs($user)->get('/')->assertRedirect('/fr');
    }

    public function test_unprefixed_public_page_stays_english_and_self_canonical_despite_locale_signal(): void
    {
        // Even with a German browser signal, the bare /contact URL is the English
        // x-default: it must render English and canonicalise to itself, never /de.
        $response = $this->get('/contact', ['Accept-Language' => 'de-DE,de;q=0.9']);

        // Canonical points to itself and the page renders English, never /de.
        $response->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/contact').'">', false)
            ->assertSee('<html lang="en"', false)
            ->assertDontSee('<link rel="canonical" href="'.url('/de/contact').'">', false);
    }

    public function test_translated_prefixed_public_page_is_self_canonical_to_its_locale(): void
    {
        // Turkish has the full pages.contact section, so /tr/contact is a real
        // translation and canonicalises to itself. See PublicPageSeoTest for the
        // untranslated counterpart.
        $this->get('/tr/contact')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/tr/contact').'">', false);
    }
}
