<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GameController;
use App\Models\Article;
use App\Models\LearningPathExercise;
use App\Models\TeacherProfile;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Public marketing / content pages. Only guest-accessible URLs belong
     * here — everything auth-gated is excluded (and disallowed in robots.txt).
     */
    /**
     * Landing page per locale — rendered with xhtml:link hreflang alternates
     * in the sitemap ('en' lives at '/', which is also the x-default).
     */
    private const LANDING_PATHS = [
        'en' => '/',
        'de' => '/de',
        'fr' => '/fr',
        'es' => '/es',
        'pt' => '/pt',
        'tr' => '/tr',
        'it' => '/it',
    ];

    private const STATIC_PATHS = [
        '/pricing',
        '/pricing/teachers-and-schools',
        '/students',
        '/teachers',
        '/schools',
        '/piano-learners',
        '/request-demo',
        '/help',
        '/how-it-works',
        '/find-teachers',
        '/learn',
        '/exercise-setup',
        '/faq',
        '/blog',
        '/ear-training-guide',
        '/music-theory-basics',
        '/contact',
        '/about',
        '/press',
        '/partners',
        '/games',
        '/piano-studio',
        '/privacy-policy',
        '/terms-of-service',
        '/cookie-policy',
        '/subscription-terms',
        '/refund-policy',
    ];

    public function index(): Response
    {
        $landingUrls = array_map(fn (string $path) => url($path), self::LANDING_PATHS);

        // Public template pages with per-locale variants. Each is emitted as its
        // own <url> per locale with the full hreflang alternate set (see the
        // sitemap view). Their English paths are dropped from STATIC_PATHS below
        // so they are never listed twice.
        // A locale is listed only once it actually has the page's translation:
        // an untranslated /{locale} URL is English copy at a second address, so
        // submitting it invites Google to fold it into the English page and log
        // a "Duplicate, Google chose a different canonical" error. The page's own
        // <head> canonicalises it to English for the same reason.
        // Blog posts live in their own registry but are localized on exactly the
        // same terms, so they join the same hreflang-carrying URL set.
        $localizedPaths = array_merge(
            array_keys((array) config('locales.public_pages')),
            array_map(fn (string $slug) => '/blog/'.$slug, array_keys((array) config('blog.posts')))
        );
        $localizedUrls = array_map(function (string $path) {
            $set = ['en' => locale_url($path, 'en')];
            foreach (config('locales.prefixed') as $locale) {
                if (locale_page_translated($path, $locale)) {
                    $set[$locale] = locale_url($path, $locale);
                }
            }

            return $set;
        }, $localizedPaths);

        $staticUrls = array_map(
            fn (string $path) => url($path),
            array_values(array_diff(self::STATIC_PATHS, $localizedPaths))
        );

        // Only published articles — drafts/pending must never appear here.
        $articles = Article::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderBy('slug')
            ->get();

        $gameUrls = array_map(
            fn (string $slug) => route('games.show', $slug),
            array_keys(GameController::GAMES)
        );

        // Practice pages and Learning Path lessons are guest-accessible
        // (daily guest quotas) and indexable.
        $practiceUrls = array_map(
            fn (string $slug) => url('/practice/'.$slug),
            [
                'single-note-practice', 'melodic-interval-practice', 'harmonic-interval-practice',
                'interval-direction-practice', 'interval-comparison-practice', 'interval-construction-practice',
                'chord-practice', 'scale-practice', 'rhythm-practice', 'melodic-dictation',
            ]
        );

        // Lessons carry a real <lastmod>: their copy and config genuinely change
        // when the curriculum is revised, so it is worth telling crawlers. Static
        // marketing pages deliberately get none — a made-up timestamp is worse
        // than no timestamp, because Google stops trusting the whole file.
        $lessons = LearningPathExercise::where('is_active', true)
            ->select(['slug', 'updated_at'])
            ->orderBy('slug')
            ->get()
            ->map(fn (LearningPathExercise $lesson) => [
                'loc' => route('learning-path.show', $lesson->slug),
                'lastmod' => $lesson->updated_at?->toAtomString(),
            ])
            ->all();

        // Only approved, publicly visible teacher profiles are listed —
        // draft/rejected/suspended/hidden profiles must never appear here.
        $profiles = TeacherProfile::publiclyVisible()
            ->select(['slug', 'entity_type', 'updated_at'])
            ->orderBy('slug')
            ->get();

        $xml = view('sitemap', [
            'landingUrls' => $landingUrls,
            'localizedUrls' => $localizedUrls,
            'staticUrls' => array_merge($staticUrls, $practiceUrls),
            'lessons' => $lessons,
            'articles' => $articles,
            'gameUrls' => $gameUrls,
            'profiles' => $profiles,
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
