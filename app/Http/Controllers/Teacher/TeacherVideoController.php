<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherVideoRequest;
use App\Models\TeacherVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherVideoController extends Controller
{
    public function store(StoreTeacherVideoRequest $request): RedirectResponse
    {
        $profile = $request->user()->teacherProfile;
        abort_if(! $profile, 404);
        $this->authorize('update', $profile);

        $validated = $request->validated();

        $profile->videos()->create([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'youtube_id' => TeacherVideo::extractYoutubeId($validated['url']),
            'description' => $validated['description'] ?? null,
            'sort_order' => ($profile->videos()->max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('status', 'video-saved');
    }

    public function update(StoreTeacherVideoRequest $request, TeacherVideo $video): RedirectResponse
    {
        $this->authorize('update', $video->teacherProfile);

        $validated = $request->validated();

        $video->update([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'youtube_id' => TeacherVideo::extractYoutubeId($validated['url']),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('status', 'video-saved');
    }

    public function destroy(Request $request, TeacherVideo $video): RedirectResponse
    {
        $this->authorize('update', $video->teacherProfile);

        $video->delete();

        return back()->with('status', 'video-deleted');
    }
}
