<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    private array $staticPages = [
        [
            'title' => 'Piano Studio',
            'description' => 'Interactive piano keyboard for practice and exploration.',
            'url' => '/piano-studio',
            'icon' => 'piano',
            'color' => 'blue',
        ],
        [
            'title' => 'AI Exercises',
            'description' => 'AI-generated personalized ear training exercises.',
            'url' => '/ai-exercises',
            'icon' => 'sparkles',
            'color' => 'purple',
        ],
        [
            'title' => 'Music Games',
            'description' => 'Fun games to sharpen your music skills.',
            'url' => '/games',
            'icon' => 'gamepad-2',
            'color' => 'green',
        ],
        [
            'title' => 'Exercise Setup Studio',
            'description' => 'Customize and configure your own practice sessions.',
            'url' => '/exercise-setup',
            'icon' => 'wand-sparkles',
            'color' => 'orange',
        ],
        [
            'title' => 'Learning Path',
            'description' => 'Structured lessons that guide you from beginner to advanced.',
            'url' => '/dashboard',
            'icon' => 'route',
            'color' => 'purple',
        ],
        [
            'title' => 'Progress',
            'description' => 'Track your ear training progress over time.',
            'url' => '/progress',
            'icon' => 'trending-up',
            'color' => 'teal',
        ],
        [
            'title' => 'Blog & Resources',
            'description' => 'Articles on ear training, music theory, and practice tips.',
            'url' => '/blog',
            'icon' => 'book-open',
            'color' => 'rose',
        ],
        [
            'title' => 'FAQ',
            'description' => 'Frequently asked questions about Harmoniva.',
            'url' => '/faq',
            'icon' => 'help-circle',
            'color' => 'gray',
        ],
        [
            'title' => 'Pricing',
            'description' => 'Explore free and premium plans.',
            'url' => '/pricing',
            'icon' => 'tag',
            'color' => 'green',
        ],
        [
            'title' => 'Ear Training Guide',
            'description' => 'Comprehensive guide to ear training for musicians.',
            'url' => '/ear-training-guide',
            'icon' => 'headphones',
            'color' => 'purple',
        ],
        [
            'title' => 'Music Theory Basics',
            'description' => 'Foundation concepts in music theory explained clearly.',
            'url' => '/music-theory-basics',
            'icon' => 'music',
            'color' => 'blue',
        ],
    ];

    public function index(Request $request): View
    {
        $q = trim($request->input('q', ''));
        $articles = collect();
        $practices = collect();
        $exercises = collect();
        $categories = collect();
        $pages = collect();

        if (mb_strlen($q) >= 2) {
            $articles = Article::published()
                ->where(function ($query) use ($q) {
                    $query->where('title', 'LIKE', "%{$q}%")
                          ->orWhere('excerpt', 'LIKE', "%{$q}%")
                          ->orWhere('body', 'LIKE', "%{$q}%");
                })
                ->latest('published_at')
                ->take(10)
                ->get();

            $practices = Practice::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('description', 'LIKE', "%{$q}%")
                      ->orWhere('type', 'LIKE', "%{$q}%");
            })
            ->take(10)
            ->get();

            $exercises = LearningPathExercise::where('is_active', true)
                ->where(function ($query) use ($q) {
                    $query->where('title', 'LIKE', "%{$q}%")
                          ->orWhere('description', 'LIKE', "%{$q}%");
                })
                ->with('category')
                ->orderBy('sort_order')
                ->take(15)
                ->get();

            $categories = ExerciseCategory::active()
                ->where(function ($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('description', 'LIKE', "%{$q}%");
                })
                ->take(12)
                ->get();

            $lowerQ = mb_strtolower($q);
            $pages = collect(array_filter($this->staticPages, function ($page) use ($lowerQ) {
                return str_contains(mb_strtolower($page['title']), $lowerQ)
                    || str_contains(mb_strtolower($page['description']), $lowerQ);
            }));
        }

        $totalResults = $articles->count()
            + $practices->count()
            + $exercises->count()
            + $categories->count()
            + $pages->count();

        return view('search.index', compact(
            'q', 'articles', 'practices', 'exercises', 'categories', 'pages', 'totalResults'
        ));
    }
}
