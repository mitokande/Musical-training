<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherMedia;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Notifications\Teacher\StudentDocumentShared;
use App\Services\Teacher\CrmQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherMediaController extends Controller
{
    // Photos & Certificates gallery: images shown as a lightbox, PDF certificates open in a new tab.
    private const PHOTO_MIMES = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    // Private document archive: PDFs, lesson material and scores.
    private const DOCUMENT_MIMES = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    // 10 MB (megabytes) upload cap.
    private const MAX_KB = 10240;

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->teacherProfile;
        abort_if(! $profile, 404);
        $this->authorize('update', $profile);

        $isPhoto = $request->input('kind') === 'photo';

        // Free-tier cap on the private document archive (PDF etc.).
        if (! $isPhoto) {
            $quota = app(CrmQuotaService::class);
            if (! $quota->canUploadDocument($request->user())) {
                return back()->withErrors([
                    'file' => __('teacher.limits.documents_reached', [
                        'limit' => $quota->limit($request->user(), 'max_documents'),
                    ]),
                ]);
            }
        }

        $validated = $request->validate([
            'kind' => ['required', 'in:photo,document'],
            'title' => ['nullable', 'string', 'max:255'],
            // Photos/certificates are always public; documents pick an archive scope.
            'visibility' => $isPhoto
                ? ['nullable']
                : ['required', 'in:private,students,shared'],
            'file' => [
                'required',
                'file',
                'max:'.self::MAX_KB,
                'mimes:'.implode(',', $isPhoto ? self::PHOTO_MIMES : self::DOCUMENT_MIMES),
            ],
        ]);

        // Photos & certificates are a public profile feature; documents keep their chosen scope.
        $visibility = $isPhoto ? TeacherMedia::VISIBILITY_PUBLIC : $validated['visibility'];

        $file = $request->file('file');

        // Capture metadata before moving the file — once moved/stored the temp
        // file is gone and getSize()/getClientOriginalName() would fail.
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize() ?: 0;

        if ($visibility === TeacherMedia::VISIBILITY_PUBLIC) {
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
            'visibility' => $visibility,
            'sort_order' => ($profile->media()->max('sort_order') ?? 0) + 1,
        ]);

        return back()->with('status', 'media-saved');
    }

    /**
     * Edit an existing media item's metadata. The stored file itself is never
     * swapped here (delete + re-upload for that); only the title — and, for
     * private document-archive items, the visibility scope — are editable.
     */
    public function update(Request $request, TeacherMedia $media): RedirectResponse
    {
        $this->authorize('update', $media->teacherProfile);

        $isPhoto = $media->kind === 'photo';

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            // Photos/certificates stay public; documents may change archive scope.
            'visibility' => $isPhoto
                ? ['nullable']
                : ['required', 'in:private,students,shared'],
        ]);

        $media->update([
            'title' => $validated['title'] ?? null,
        ] + ($isPhoto ? [] : ['visibility' => $validated['visibility']]));

        return back()->with('status', 'media-saved');
    }

    /**
     * Share a document from the archive with specific students ("paylaştıklarım").
     * Only the teacher's active students are eligible; newly-added students get a
     * notification and download access, existing shares are left untouched.
     */
    public function share(Request $request, TeacherMedia $media): RedirectResponse
    {
        $this->authorize('update', $media->teacherProfile);

        $validated = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer'],
        ]);

        $teacherUserId = $media->teacherProfile->user_id;

        // Restrict to students the teacher actually has an active relationship with.
        $eligibleIds = TeacherStudentRelationship::query()
            ->active()
            ->where('teacher_id', $teacherUserId)
            ->whereIn('student_id', $validated['student_ids'] ?? [])
            ->pluck('student_id')
            ->all();

        $alreadyShared = $media->sharedStudents()->pluck('users.id')->all();
        $newlyShared = array_diff($eligibleIds, $alreadyShared);

        $media->sharedStudents()->syncWithoutDetaching($eligibleIds);

        if (! empty($newlyShared)) {
            $teacher = $request->user();
            User::whereIn('id', $newlyShared)->get()
                ->each(fn (User $student) => $student->notify(new StudentDocumentShared($media, $teacher)));
        }

        return back()->with('status', 'media-shared');
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
