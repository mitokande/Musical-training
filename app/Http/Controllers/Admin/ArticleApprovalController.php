<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleApprovalController extends Controller
{
    public function index(): View
    {
        $pendingArticles = Article::pending()->with('author')->latest()->get();
        $allArticles = Article::with('author')->latest()->paginate(20);

        return view('admin.articles.index', [
            'pendingArticles' => $pendingArticles,
            'allArticles' => $allArticles,
        ]);
    }

    public function show(Article $article): View
    {
        $article->load('author');

        return view('admin.articles.show', compact('article'));
    }

    public function approve(Article $article): RedirectResponse
    {
        $article->update([
            'status' => 'published',
            'published_at' => now(),
            'admin_note' => null,
        ]);

        return redirect()->back()->with('success', "'{$article->title}' basariyla yayinlandi.");
    }

    public function reject(Request $request, Article $article): RedirectResponse
    {
        $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $article->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return redirect()->back()->with('success', "'{$article->title}' reddedildi.");
    }
}
