<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCreative;
use App\Services\AdStudio\AdCreativeService;
use App\Services\AdStudio\AdTemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Ad Studio: author a HyperFrames ad creative from a shipped template,
 * generate its narration, and render it on this server.
 *
 * The editor itself is a Livewire component; this controller owns the list, the
 * lifecycle actions, and serving the generated artefacts — which live outside
 * the public root, so every file leaves through a route behind admin middleware
 * rather than a URL anyone could guess.
 */
class AdStudioController extends Controller
{
    public function __construct(private readonly AdCreativeService $service)
    {
        // Checked here rather than around the route group so the kill switch
        // still works with a cached route table.
        abort_unless(config('ad_studio.enabled'), 404);
    }

    public function index(Request $request, AdTemplateRegistry $registry)
    {
        $creatives = AdCreative::query()
            ->with('author:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.ad-studio.index', [
            'creatives' => $creatives,
            'templates' => $registry->all(),
            'statuses' => [
                AdCreative::STATUS_DRAFT => 'Draft',
                AdCreative::STATUS_BUILT => 'Ready to render',
                AdCreative::STATUS_QUEUED => 'Queued',
                AdCreative::STATUS_RENDERING => 'Rendering',
                AdCreative::STATUS_RENDERED => 'Rendered',
                AdCreative::STATUS_FAILED => 'Failed',
            ],
        ]);
    }

    public function store(Request $request, AdTemplateRegistry $registry)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'template' => ['required', 'string', Rule::in(array_keys($registry->all()))],
        ]);

        $creative = $this->service->create(
            $validated['name'],
            $validated['template'],
            $request->user(),
        );

        return redirect()
            ->route('admin.ad-studio.edit', $creative)
            ->with('success', 'Creative created from the shipped template — edit the copy, then build.');
    }

    public function edit(AdCreative $adCreative)
    {
        return view('admin.ad-studio.edit', ['creative' => $adCreative]);
    }

    public function destroy(AdCreative $adCreative)
    {
        if ($adCreative->isBusy()) {
            return back()->with('error', 'That creative is mid-build or mid-render. Wait for it to finish before deleting it.');
        }

        $name = $adCreative->name;
        $this->service->delete($adCreative);

        return redirect()
            ->route('admin.ad-studio.index')
            ->with('success', "Deleted [$name] and its generated project.");
    }

    /** The finished MP4. Downloaded, not streamed inline: it is a deliverable. */
    public function download(AdCreative $adCreative): BinaryFileResponse
    {
        abort_unless($adCreative->hasRender(), 404);

        return response()->download(
            $adCreative->absoluteRenderPath(),
            Str::slug($adCreative->name).'-'.round((float) $adCreative->duration_seconds).'s.mp4',
        );
    }

    /** Inline playback for the panel's <video> preview. */
    public function watch(AdCreative $adCreative): BinaryFileResponse
    {
        abort_unless($adCreative->hasRender(), 404);

        return response()->file($adCreative->absoluteRenderPath(), ['Content-Type' => 'video/mp4']);
    }

    /**
     * One snapshot PNG from the creative's own project.
     *
     * The filename is validated against a strict pattern and resolved inside the
     * creative's snapshot directory — these paths are built from a request
     * parameter, so path traversal is the obvious failure mode to close.
     */
    public function snapshot(AdCreative $adCreative, string $file): BinaryFileResponse|StreamedResponse
    {
        abort_unless((bool) preg_match('/^[a-z0-9._-]+\.(png|jpg)$/i', $file), 404);

        $dir = realpath($adCreative->absoluteProjectDir().'/snapshots');
        $path = $dir ? realpath("$dir/$file") : false;

        abort_unless($dir && $path && str_starts_with($path, $dir.DIRECTORY_SEPARATOR) && is_file($path), 404);

        return response()->file($path);
    }
}
