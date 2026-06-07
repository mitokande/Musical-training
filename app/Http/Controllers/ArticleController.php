<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = $request->user()->articles()->latest()->paginate(15);

        return view('articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('articles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content_type' => ['required', 'in:article,video,document,audio,sheet_music'],
            'visibility' => ['required', 'in:public,students_only,school_only,private'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $request->input('action') === 'publish' ? 'pending' : 'draft';

        $data = [
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Article::generateSlug($validated['title']),
            'body' => $validated['body'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'content_type' => $validated['content_type'],
            'visibility' => $validated['visibility'],
            'video_url' => $validated['video_url'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $status,
        ];

        if (!empty($validated['tags'])) {
            $data['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        if ($request->hasFile('audio_file')) {
            $data['audio_file'] = $request->file('audio_file')->store('articles/audio', 'public');
        }
        if ($request->hasFile('document_file')) {
            $data['document_file'] = $request->file('document_file')->store('articles/documents', 'public');
        }
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('articles/images', 'public');
        }

        Article::create($data);

        $message = $status === 'pending' ? 'Icerik onaya gonderildi.' : 'Icerik taslak olarak kaydedildi.';
        return redirect()->route('articles.index')->with('success', $message);
    }

    public function edit(Article $article): View
    {
        if ($article->author_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        if ($article->author_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content_type' => ['required', 'in:article,video,document,audio,sheet_music'],
            'visibility' => ['required', 'in:public,students_only,school_only,private'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $status = $request->input('action') === 'publish' ? 'pending' : 'draft';
        if ($article->status === 'rejected' && $status === 'pending') {
            $article->admin_note = null;
        }

        $data = [
            'title' => $validated['title'],
            'body' => $validated['body'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'content_type' => $validated['content_type'],
            'visibility' => $validated['visibility'],
            'video_url' => $validated['video_url'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $status,
        ];

        if (!empty($validated['tags'])) {
            $data['tags'] = array_map('trim', explode(',', $validated['tags']));
        } else {
            $data['tags'] = null;
        }

        foreach (['audio_file', 'document_file', 'featured_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                if ($article->$fileField) {
                    Storage::disk('public')->delete($article->$fileField);
                }
                $subdir = match ($fileField) {
                    'audio_file' => 'articles/audio',
                    'document_file' => 'articles/documents',
                    'featured_image' => 'articles/images',
                };
                $data[$fileField] = $request->file($fileField)->store($subdir, 'public');
            }
        }

        $article->update($data);

        return redirect()->route('articles.index')->with('success', 'Icerik guncellendi.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->author_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        foreach (['audio_file', 'document_file', 'featured_image'] as $fileField) {
            if ($article->$fileField) {
                Storage::disk('public')->delete($article->$fileField);
            }
        }

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Icerik silindi.');
    }
}
