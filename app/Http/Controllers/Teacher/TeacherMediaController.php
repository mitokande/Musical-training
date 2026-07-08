<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherMediaController extends Controller
{
    private const PHOTO_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    private const DOCUMENT_MIMES = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->teacherProfile;
        abort_if(! $profile, 404);
        $this->authorize('update', $profile);

        $validated = $request->validate([
            'kind' => ['required', 'in:photo,document'],
            'title' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'in:public,private,shared'],
            'file' => [
                'required',
                'file',
                'max:8192',
                'mimes:'.implode(',', $request->input('kind') === 'photo' ? self::PHOTO_MIMES : self::DOCUMENT_MIMES),
            ],
        ]);

        $file = $request->file('file');

        // Capture metadata before moving the file — once moved/stored the temp
        // file is gone and getSize()/getClientOriginalName() would fail.
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize() ?: 0;

        if ($validated['visibility'] === 'public') {
            // Public media lives under public/ following the avatar convention.
            $filename = $file->hashName();
            $destDir = public_path('images/teacher/media');
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $disk = 'public_dir';
            $path = 'images/teacher/media/'.$filename;
        } else {
            // Private and shared files never get a direct URL; they are served
            // through the authorized download route only.
            $path = $file->store('teacher-documents/'.$profile->id, 'local');
            $disk = 'local';
        }

        $profile->media()->create([
            'kind' => $validated['kind'],
            'disk' => $disk,
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'title' => $validated['title'] ?? null,
            'visibility' => $validated['visibility'],
            'sort_order' => ($profile->media()->max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('status', 'media-saved');
    }

    /** Authorized download: owner/admin, approved students (shared), recipients. */
    public function download(Request $request, TeacherMedia $media): StreamedResponse
    {
        abort_unless($media->canBeDownloadedBy($request->user()), 403);

        abort_unless($media->disk === 'local' && Storage::disk('local')->exists($media->path), 404);

        return Storage::disk('local')->download($media->path, $media->original_name);
    }

    public function destroy(Request $request, TeacherMedia $media): RedirectResponse
    {
        $this->authorize('update', $media->teacherProfile);

        if ($media->disk === 'public_dir') {
            $file = public_path($media->path);
            if (file_exists($file)) {
                @unlink($file);
            }
        } else {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return back()->with('status', 'media-deleted');
    }
}
