<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

/**
 * Long-form blog posts at /blog/{slug} and /{locale}/blog/{slug}.
 *
 * What is worth protecting here is the URL identity: one post, one address per
 * language, every other spelling redirecting to it, and the canonical/hreflang
 * set naming exactly those addresses. Those rules are spread across the config,
 * the helpers, the SEO service and the sitemap, so they are asserted end to end.
 */
class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    private function posts(): array
    {
        return (array) config('blog.posts');
    }

    public function test_a_post_is_served_at_its_english_slug(): void
    {
        foreach ($this->posts() as $key => $post) {
            $this->get('/blog/'.$key)
                ->assertOk()
                ->assertSee(__('blog.'.$post['section'].'.title', [], 'en'), false);
        }
    }

    public function test_an_unknown_slug_is_not_found(): void
    {
        $this->get('/blog/no-such-article')->assertNotFound();
        $this->get('/tr/blog/no-such-article')->assertNotFound();
    }

    public function test_a_translated_post_is_served_at_its_own_localized_slug(): void
    {
        foreach ($this->posts() as $key => $post) {
            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                $this->get('/'.$locale.'/blog/'.$slug)
                    ->assertOk()
                    ->assertSee(__('blog.'.$post['section'].'.title', [], $locale), false);
            }
        }
    }

    public function test_another_languages_slug_redirects_permanently(): void
    {
        foreach ($this->posts() as $key => $post) {
            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                // The English slug under a locale that has its own.
                $this->get('/'.$locale.'/blog/'.$key)
                    ->assertRedirect(url('/'.$locale.'/blog/'.$slug))
                    ->assertStatus(301);

                // A localized slug at the English root.
                $this->get('/blog/'.$slug)
                    ->assertRedirect(url('/blog/'.$key))
                    ->assertStatus(301);

                // A localized slug under a *different* locale prefix.
                $other = collect(config('locales.prefixed'))
                    ->reject(fn ($l) => array_key_exists($l, (array) ($post['slugs'] ?? [])))
                    ->first();

                if ($other !== null) {
                    $this->get('/'.$other.'/blog/'.$slug)
                        ->assertRedirect(url('/'.$other.'/blog/'.$key))
                        ->assertStatus(301);
                }
            }
        }
    }

    public function test_canonical_and_hreflang_name_each_languages_own_slug(): void
    {
        foreach ($this->posts() as $key => $post) {
            $slugs = ['en' => $key] + array_map(
                fn ($s) => $s,
                (array) ($post['slugs'] ?? [])
            );

            foreach ($slugs as $locale => $slug) {
                $path = $locale === 'en' ? '/blog/'.$slug : '/'.$locale.'/blog/'.$slug;
                $response = $this->get($path);
                $response->assertOk();

                $html = $response->getContent();

                $this->assertStringContainsString(
                    '<link rel="canonical" href="'.url($path).'">',
                    $html,
                    "$path should canonicalise to itself"
                );

                foreach ($slugs as $altLocale => $altSlug) {
                    $altPath = $altLocale === 'en' ? '/blog/'.$altSlug : '/'.$altLocale.'/blog/'.$altSlug;
                    $this->assertStringContainsString(
                        'hreflang="'.$altLocale.'" href="'.url($altPath).'"',
                        $html,
                        "$path should advertise $altLocale at $altPath"
                    );
                }
            }
        }
    }

    public function test_locale_url_rewrites_the_slug_when_switching_language(): void
    {
        foreach ($this->posts() as $key => $post) {
            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                // From either spelling, in either direction.
                $this->assertSame(url('/'.$locale.'/blog/'.$slug), locale_url('/blog/'.$key, $locale));
                $this->assertSame(url('/blog/'.$key), locale_url('/blog/'.$slug, 'en'));
                $this->assertSame(url('/'.$locale.'/blog/'.$slug), locale_url('/blog/'.$slug, $locale));
            }

            // A fragment survives the rewrite — the table of contents links with one.
            $this->assertSame(url('/blog/'.$key.'#s3'), locale_url('/blog/'.$key.'#s3', 'en'));
        }
    }

    public function test_the_sitemap_lists_every_language_with_the_right_slug(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach ($this->posts() as $key => $post) {
            $this->assertStringContainsString('<loc>'.url('/blog/'.$key).'</loc>', $xml);

            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                $this->assertStringContainsString('<loc>'.url('/'.$locale.'/blog/'.$slug).'</loc>', $xml);
                $this->assertStringNotContainsString('<loc>'.url('/'.$locale.'/blog/'.$key).'</loc>', $xml);
            }
        }
    }

    public function test_slugs_are_unique_across_posts_and_languages(): void
    {
        $seen = [];

        foreach ($this->posts() as $key => $post) {
            foreach (array_merge([$key], array_values((array) ($post['slugs'] ?? []))) as $slug) {
                $this->assertArrayNotHasKey(
                    $slug,
                    $seen,
                    "Slug '$slug' is claimed by both '".($seen[$slug] ?? '?')."' and '$key'; "
                    .'blog_post_for_slug() would silently resolve to whichever comes first.'
                );
                $seen[$slug] = $key;
            }
        }
    }

    public function test_a_localized_slug_is_only_declared_once_its_translation_is_complete(): void
    {
        foreach ($this->posts() as $key => $post) {
            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                $this->assertTrue(
                    locale_page_translated('/blog/'.$key, $locale),
                    "config('blog.posts.$key.slugs.$locale') promises a $locale URL, but "
                    ."resources/lang/$locale/blog.php does not carry the translation yet. "
                    .'The slug would advertise a language the body does not speak.'
                );
            }
        }
    }

    public function test_every_locale_with_a_translation_defines_the_whole_section(): void
    {
        foreach ($this->posts() as $key => $post) {
            $source = Lang::get('blog.'.$post['section'], [], 'en', false);

            foreach ((array) ($post['slugs'] ?? []) as $locale => $slug) {
                $target = Lang::get('blog.'.$post['section'], [], $locale, false);

                $this->assertSame(
                    [],
                    array_diff_key($this->flatten($source), $this->flatten($target)),
                    "resources/lang/$locale/blog.php is missing keys from '{$post['section']}'."
                );
            }
        }
    }

    /** @return array<string, true> */
    private function flatten(array $items, string $prefix = ''): array
    {
        $flat = [];
        foreach ($items as $key => $value) {
            $dotted = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $flat += is_array($value) ? $this->flatten($value, $dotted) : [$dotted => true];
        }

        return $flat;
    }
}
