<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A guest's language must last as long as the things it labels.
 *
 * Guest usage quotas live in the one-year `harmoniva_guest_id` cookie
 * (UsageQuotaService) while the chosen language used to live only in the
 * 120-minute session. When the session lapsed first, the quota survived but the
 * language did not: a visitor reading the site in English hit their daily game
 * limit and got the "limit reached" screen — which replaces the entire game
 * area — rendered in whatever language their browser asked for.
 *
 * These tests pin both halves of the fix: the language is mirrored into a
 * cookie of the same lifetime, and a visitor with no language signal at all
 * gets English rather than a guess.
 */
class GuestLocalePersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function langOf(string $html): string
    {
        preg_match('/<html lang="([^"]+)"/', $html, $m);

        return $m[1] ?? '';
    }

    public function test_switching_language_stores_a_long_lived_plain_cookie(): void
    {
        $this->post('/language/switch', ['locale' => 'en'])
            // Plain, not encrypted: it holds a language code, not a secret, and
            // encrypting it would cost ~300 bytes on every request to the domain.
            ->assertPlainCookie(SetLocale::LOCALE_COOKIE, 'en');
    }

    public function test_stored_language_outlives_the_session_and_beats_the_browser_header(): void
    {
        // The reported bug, end to end: session gone (expired), guest cookie
        // still present, Turkish browser — the page must stay English.
        $html = $this->withUnencryptedCookie(SetLocale::LOCALE_COOKIE, 'en')
            ->get('/games/note-fall', ['Accept-Language' => 'tr-TR,tr;q=0.9'])
            ->assertOk()
            ->getContent();

        $this->assertSame('en', $this->langOf($html));
        $this->assertStringContainsString('Daily limit reached', $html);
    }

    public function test_visitor_with_no_language_signal_gets_english(): void
    {
        // No cookie, no session, no Accept-Language. IP geolocation used to
        // answer here and could return any language; the site's source language
        // is the only correct answer now.
        //
        // The blank header is deliberate: Symfony's test client injects
        // `Accept-Language: en-us,en;q=0.5` by default, so simply omitting it
        // tests the English-browser case rather than the signal-less one.
        $html = $this->get('/games/note-fall', ['Accept-Language' => ''])
            ->assertOk()
            ->getContent();

        $this->assertSame('en', $this->langOf($html));
    }

    public function test_browser_language_is_still_honoured_when_nothing_is_stored(): void
    {
        $html = $this->get('/games/note-fall', ['Accept-Language' => 'de-DE,de;q=0.9'])
            ->assertOk()
            ->getContent();

        $this->assertSame('de', $this->langOf($html));
    }

    public function test_browser_language_is_persisted_so_it_survives_the_session(): void
    {
        $this->get('/games/note-fall', ['Accept-Language' => 'es-ES,es;q=0.9'])
            ->assertPlainCookie(SetLocale::LOCALE_COOKIE, 'es');
    }

    public function test_signal_less_visitor_is_not_pinned_to_english(): void
    {
        // Crawlers and prefetches send no Accept-Language. Writing a cookie for
        // them would lock English in for a real visitor arriving later on the
        // same client, so the fallback must stay unpersisted.
        $this->get('/games/note-fall', ['Accept-Language' => ''])
            ->assertCookieMissing(SetLocale::LOCALE_COOKIE);
    }

    public function test_tampered_cookie_falls_through_to_the_browser_language(): void
    {
        $html = $this->withUnencryptedCookie(SetLocale::LOCALE_COOKIE, 'xx-hacked')
            ->get('/games/note-fall', ['Accept-Language' => 'de-DE,de;q=0.9'])
            ->assertOk()
            ->getContent();

        $this->assertSame('de', $this->langOf($html));
    }
}
