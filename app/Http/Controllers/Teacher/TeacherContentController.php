<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Content hub inside the CRM: articles, videos, and documents in one place. */
class TeacherContentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->teacherProfile;

        return view('teacher.content.index', [
            'articles' => Article::where('author_id', $user->id)->latest()->get(),
            'videos' => $profile?->videos()->orderBy('sort_order')->get() ?? collect(),
            'documents' => $profile?->media()->where('kind', 'document')->orderByDesc('created_at')->get() ?? collect(),
            'photos' => $profile?->media()->where('kind', 'photo')->orderByDesc('created_at')->get() ?? collect(),
            'hasProfile' => (bool) $profile,
        ]);
    }
}
