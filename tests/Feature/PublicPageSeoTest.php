<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Canonical / hreflang / <html lang> rules for the localized public pages.
 *
 * The /{locale} route group registers every page in config('locales.public_pages')
 * for every prefixed locale, whether or not that locale has the strings. A locale
 * missing its `pages.*` section renders English fallback copy at a second URL —
 * Google reads that as a duplicate, ignores the hreflang claim, and reports
 * "Duplicate, Google chose a different canonical than the user". So an untranslated
 * variant must canonicalise to English, drop out of the alternate set and the
 * sitemap, and declare <html lang="en">; a translated one keeps all of it.
 *
 * The untranslated half is driven by pointing a page at a section name no locale
 * defines, rather than by naming a locale that happens to be behind on
 * translation. Otherwise every batch of translated copy would break these tests —
 * which is exactly what the gate is designed to make unnecessary.
 */
class PublicPageSeoTest extends TestCase
{
    // The sitemap queries articles / lessons / teacher profiles.
    use RefreshDatabase;

    /** Point /contact at a `pages.*` section that exists in no locale. */
    private function withUntranslatedContactPage(): void
    {
        config(['locales.page_sections' => array_merge(
            (array) config('locales.page_sections'),
            ['/contact' => 'section_no_locale_defines'],
        )]);
    }

    public function test_untranslated_locale_page_canonicalises_to_english(): void
    {
        $this->withUntranslatedContactPage();

        $this->get('/de/contact')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/contact').'">', false)
            ->assertDontSee('<link rel="canonical" href="'.url('/de/contact').'">', false);
    }

    public function test_untranslated_locale_page_declares_english_html_lang(): void
    {
        // The body is English fallback copy — claiming lang="de" is the duplicate
        // signal itself, not a cosmetic detail.
        $this->withUntranslatedContactPage();

        $this->get('/de/contact')
            ->assertOk()
            ->assertSee('<html lang="en">', false);
    }

    public function test_untranslated_locale_is_never_advertised_as_an_alternate(): void
    {
        $this->withUntranslatedContactPage();

        // With no locale translated, the English page has no alternate to claim.
        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('hreflang=', false);
    }

    public function test_translated_locale_page_keeps_its_own_canonical_and_lang(): void
    {
        // Turkish has the full pages.contact section and is the reference locale
        // for "fully translated" throughout these tests.
        $this->get('/tr/contact')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/tr/contact').'">', false)
            ->assertSee('<html lang="tr">', false);
    }

    public function test_translated_locale_page_advertises_the_full_alternate_set(): void
    {
        $this->get('/tr/contact')
            ->assertOk()
            ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/contact').'">', false)
            ->assertSee('<link rel="alternate" hreflang="tr" href="'.url('/tr/contact').'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/contact').'">', false);
    }

    public function test_page_outside_public_pages_declares_no_alternates(): void
    {
        // /games has no localized variant; claiming one would point Google at a
        // URL that does not exist.
        $this->get('/games')
            ->assertOk()
            ->assertDontSee('hreflang=', false);
    }

    public function test_sitemap_lists_translated_locales_only(): void
    {
        $this->withUntranslatedContactPage();

        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>'.url('/contact').'</loc>', $sitemap);
        $this->assertStringNotContainsString('<loc>'.url('/de/contact').'</loc>', $sitemap);
        $this->assertStringNotContainsString('<loc>'.url('/tr/contact').'</loc>', $sitemap);

        // A page left on its real section still lists the locales that have it.
        $this->assertStringContainsString('<loc>'.url('/tr/faq').'</loc>', $sitemap);
    }

    public function test_helper_reports_translation_state_per_section(): void
    {
        // English is the source and is always "translated".
        $this->assertTrue(locale_page_translated('/pricing', 'en'));
        $this->assertTrue(locale_page_translated('/pricing', 'tr'));

        // A section no locale defines can never be claimed as a translation.
        $this->withUntranslatedContactPage();
        $this->assertFalse(locale_page_translated('/contact', 'tr'));
    }

    public function test_a_section_below_the_threshold_is_rejected(): void
    {
        // Raise the bar past 100%: even a complete section must now fail, proving
        // the ratio is really consulted rather than mere section existence.
        config(['locales.page_translation_threshold' => 1.01]);

        $this->assertFalse(locale_page_translated('/pricing', 'tr'));
        $this->assertTrue(locale_page_translated('/pricing', 'en'));
    }

    public function test_unknown_path_is_not_claimed_as_translated(): void
    {
        $this->assertFalse(locale_page_translated('/not-a-public-page', 'tr'));
    }
}
