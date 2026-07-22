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
        $staticUrls = array_map(fn (string $path) => url($path), self::STATIC_PATHS);

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

        $lessonUrls = LearningPathExercise::where('is_active', true)
            ->orderBy('slug')
            ->pluck('slug')
            ->map(fn (string $slug) => route('learning-path.show', $slug))
            ->all();

        // Only approved, publicly visible teacher profiles are listed —
        // draft/rejected/suspended/hidden profiles must never appear here.
        $profiles = TeacherProfile::publiclyVisible()
            ->select(['slug', 'entity_type', 'updated_at'])
            ->orderBy('slug')
            ->get();

        $xml = view('sitemap', [
            'landingUrls' => $landingUrls,
            'staticUrls' => array_merge($staticUrls, $practiceUrls, $lessonUrls),
            'articles' => $articles,
            'gameUrls' => $gameUrls,
            'profiles' => $profiles,
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
