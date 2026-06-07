<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ContentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::with(['author', 'contentCategory'])
            ->when($request->content_type, fn($q, $type) => $q->where('content_type', $type))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->category_id, fn($q, $catId) => $q->where('category_id', $catId))
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        $categories = ContentCategory::where('is_active', true)->get();

        return view('admin.content.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = ContentCategory::where('is_active', true)->get();

        return view('admin.content.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'body'             => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'content_type'     => 'required|string|in:article,video,audio,document',
            'status'           => 'required|string|in:draft,pending,published',
            'visibility'       => 'required|string|in:public,premium,private',
            'category_id'      => 'nullable|exists:content_categories,id',
            'featured_image'   => 'nullable|image|max:2048',
            'video_url'        => 'nullable|url',
            'audio_file'       => 'nullable|file|mimes:mp3,wav|max:10240',
            'document_file'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'tags'             => 'nullable|array',
            'is_featured'      => 'boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'og_image'         => 'nullable|string|max:255',
            'canonical_url'    => 'nullable|url',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['slug'] = Article::generateSlug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('articles/images', 'public');
        }
        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store('articles/audio', 'public');
        }
        if ($request->hasFile('document_file')) {
            $validated['document_file'] = $request->file('document_file')->store('articles/documents', 'public');
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        Article::create($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = ContentCategory::where('is_active', true)->get();

        return view('admin.content.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'body'             => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'content_type'     => 'required|string|in:article,video,audio,document',
            'status'           => 'required|string|in:draft,pending,published',
            'visibility'       => 'required|string|in:public,premium,private',
            'category_id'      => 'nullable|exists:content_categories,id',
            'featured_image'   => 'nullable|image|max:2048',
            'video_url'        => 'nullable|url',
            'tags'             => 'nullable|array',
            'is_featured'      => 'boolean',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords'    => 'nullable|string|max:255',
            'og_image'         => 'nullable|string|max:255',
            'canonical_url'    => 'nullable|url',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('articles/images', 'public');
        }

        $article->update($validated);

        return redirect()->route('admin.content.index')
            ->with('success', 'Content updated successfully.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('admin.content.index')
            ->with('success', 'Content deleted successfully.');
    }

    public function approve(Article $article)
    {
        $article->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Article approved and published.');
    }

    public function reject(Request $request, Article $article)
    {
        $request->validate([
            'admin_note' => 'required|string',
        ]);

        $article->update([
            'status'     => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Article rejected.');
    }
}
