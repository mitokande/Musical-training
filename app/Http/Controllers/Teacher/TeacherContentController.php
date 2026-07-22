<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\TeacherMedia;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Notifications\Teacher\StudentArticleShared;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "My Content" hub inside the CRM: a tabbed workspace for the teacher's
 * document archive (My Documents), articles (My Articles) and videos (My Videos).
 * Photos & certificates live on the profile page, not here.
 */
class TeacherContentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->teacherProfile;

        return view('teacher.content.index', [
            'articles' => Article::where('author_id', $user->id)->latest()->get(),
            'videos' => $profile?->videos()->orderBy('sort_order')->get() ?? collect(),
            'documents' => $profile
                ? $profile->media()->where('kind', TeacherMedia::KIND_DOCUMENT)
                    ->with('sharedStudents:id')->orderByDesc('created_at')->get()
                : collect(),
            'students' => $this->activeStudents($user),
            'hasProfile' => (bool) $profile,
        ]);
    }

    /** Share one of the teacher's articles with specific students (notification only). */
    public function shareArticle(Request $request, Article $article): RedirectResponse
    {
        abort_unless($article->author_id === $request->user()->id, 403);

        $validated = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer'],
        ]);

        $eligibleIds = TeacherStudentRelationship::query()
            ->active()
            ->where('teacher_id', $request->user()->id)
            ->whereIn('student_id', $validated['student_ids'] ?? [])
            ->pluck('student_id')
            ->all();

        if (! empty($eligibleIds)) {
            $teacher = $request->user();
            User::whereIn('id', $eligibleIds)->get()
                ->each(fn (User $student) => $student->notify(new StudentArticleShared($article, $teacher)));
        }

        return back()->with('status', 'article-shared');
    }

    /** The teacher's active students, for the share pickers. */
    private function activeStudents(User $teacher)
    {
        return TeacherStudentRelationship::query()
            ->active()
            ->where('teacher_id', $teacher->id)
            ->with('student:id,name,surname')
            ->get()
            ->map(fn ($rel) => $rel->student)
            ->filter()
            ->values();
    }
}
