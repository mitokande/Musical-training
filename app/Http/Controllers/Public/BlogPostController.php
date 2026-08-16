<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Services\Blog\BlogExerciseBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Long-form blog posts under /blog/{slug} (and /{locale}/blog/{slug}).
 *
 * The post itself is a blade; this action only resolves the registry entry,
 * the author profile behind the byline, and the practice questions the
 * in-article exercise boxes are seeded with.
 */
class BlogPostController extends Controller
{
    public function show(Request $request, BlogExerciseBuilder $exercises): View|RedirectResponse
    {
        // Read by name, not as a method argument: the localized route is
        // /{locale}/blog/{slug}, and Laravel hands scalar route parameters to
        // the action positionally — a `string $slug` argument would receive
        // the locale there and 404 every translated post.
        $slug = (string) $request->route('slug');

        $entry = blog_post_for_slug($slug);

        abort_if($entry === null, 404);

        $post = $entry['post'];
        $urlLocale = $this->urlLocale($request);

        // One post, one URL per language. Reaching it through another language's
        // slug — an old link, or the English slug under /tr after a translation
        // landed — is answered with a permanent redirect rather than by serving
        // the same article at a second address.
        $expectedSlug = blog_post_slug($entry['key'], $urlLocale);
        if ($slug !== $expectedSlug) {
            return redirect(locale_url('/blog/'.$entry['key'], $urlLocale), 301);
        }

        $this->forceContentLocale($request, '/blog/'.$slug);

        // Every string the post renders is addressed relative to its own
        // `blog.*` section, so the blade never repeats the section name and a
        // post can be duplicated for a new article by changing one config key.
        $section = 'blog.'.$post['section'];

        return view($post['view'], [
            'post' => $post + ['slug' => $slug, 'key' => $entry['key']],
            'postSlug' => $slug,
            'postPath' => '/blog/'.$slug,
            't' => fn (string $key, array $replace = []) => __($section.'.'.$key, $replace),
            'author' => $this->author($post['author_slug'] ?? null),
            'exercises' => $exercises,
        ]);
    }

    /** The language the URL itself declares — never a geo or session guess. */
    private function urlLocale(Request $request): string
    {
        $segments = $request->segments();

        return isset($segments[0]) && in_array($segments[0], (array) config('locales.prefixed'), true)
            ? $segments[0]
            : 'en';
    }

    /**
     * Pin the app locale to the language this URL actually publishes.
     *
     * SetLocale resolves a locale from the user/session/browser/IP, which is
     * right for the app but wrong for an article: it made the English post at
     * /blog/{slug} render Turkish navigation and Turkish category chips, and —
     * because every link is built with locale_url() — sent "All articles" to
     * /tr/blog. An article is a single-language document, so the chrome around
     * it must speak the language of the URL, and of the URL only.
     *
     * The rule matches the one <head> already follows (PublicPageSeo): a
     * /{locale} URL whose translation has not landed yet is English copy, so it
     * gets English chrome too rather than a Turkish frame around English prose.
     */
    private function forceContentLocale(Request $request, string $path): void
    {
        $urlLocale = $this->urlLocale($request);

        app()->setLocale(locale_page_translated($path, $urlLocale) ? $urlLocale : 'en');
    }

    /**
     * The teacher profile credited as the author, when it is publicly visible.
     * A hidden or missing profile degrades to a plain, unlinked byline rather
     * than pointing readers at a 404.
     */
    private function author(?string $slug): ?TeacherProfile
    {
        if ($slug === null) {
            return null;
        }

        $profile = TeacherProfile::with(['user', 'media'])
            ->publiclyVisible()
            ->where('slug', $slug)
            ->where('entity_type', TeacherProfile::ENTITY_TEACHER)
            ->first();

        return $profile;
    }
}
