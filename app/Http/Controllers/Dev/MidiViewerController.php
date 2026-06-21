<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MidiViewerController extends Controller
{
    private const DIRECTORY = 'dev-midi';

    public function index(Request $request)
    {
        $files = collect(Storage::disk('local')->files(self::DIRECTORY))
            ->filter(fn (string $path) => preg_match('/\.midi?$/i', $path))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => Storage::disk('local')->size($path),
                'uploaded_at' => Storage::disk('local')->lastModified($path),
            ])
            ->sortByDesc('uploaded_at')
            ->values();

        $selected = basename((string) $request->query('file', ''));
        if ($selected !== '' && ! $files->contains('name', $selected)) {
            $selected = '';
        }

        return view('dev.midi-viewer', [
            'files' => $files,
            'selected' => $selected !== '' ? $selected : $files->first()['name'] ?? null,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'midi' => ['required', 'file', 'max:1024', 'extensions:mid,midi'],
        ]);

        $file = $request->file('midi');

        // Standard MIDI files start with an "MThd" header chunk.
        if (file_get_contents($file->getRealPath(), false, null, 0, 4) !== 'MThd') {
            return back()->withErrors(['midi' => 'Not a valid Standard MIDI file (missing MThd header).']);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'untitled';
        $name = now()->format('Ymd-His').'_'.$base.'.mid';
        $file->storeAs(self::DIRECTORY, $name, 'local');

        return redirect()
            ->route('dev.midi.index', ['file' => $name])
            ->with('status', "Uploaded {$name}");
    }

    public function file(string $filename)
    {
        $path = self::DIRECTORY.'/'.basename($filename);

        abort_unless(preg_match('/\.midi?$/i', $filename) && Storage::disk('local')->exists($path), 404);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'audio/midi',
            'Content-Disposition' => 'inline; filename="'.basename($filename).'"',
        ]);
    }

    public function destroy(string $filename)
    {
        $path = self::DIRECTORY.'/'.basename($filename);

        abort_unless(preg_match('/\.midi?$/i', $filename) && Storage::disk('local')->exists($path), 404);

        Storage::disk('local')->delete($path);

        return redirect()->route('dev.midi.index')->with('status', 'Deleted '.basename($filename));
    }
}
