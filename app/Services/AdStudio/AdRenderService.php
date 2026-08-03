<?php

namespace App\Services\AdStudio;

use App\Models\AdCreative;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Runs the HyperFrames CLI over a built project.
 *
 * Two commands, in order, and the order matters: `check` is the gate (lint,
 * runtime errors, layout overflow, motion, WCAG contrast) and `render` is the
 * expensive part. Rendering a composition that fails check wastes minutes of CPU
 * to produce a video with a visible defect in it, so a failed check aborts.
 */
class AdRenderService
{
    public function __construct(private readonly AdProjectBuilder $builder) {}

    /**
     * Validate and render. Returns the render's relative path.
     *
     * Runs INLINE and takes minutes — the caller is the scheduled drainer, not a
     * web request and not the shared queue worker (whose 60s per-job timeout
     * would kill it mid-frame).
     */
    public function render(AdCreative $creative): array
    {
        $dir = $this->builder->projectDir($creative);

        if (! is_file("$dir/index.html")) {
            throw new AdStudioException('This creative has not been built yet — there is no composition to render.');
        }

        $this->check($dir);

        $filename = 'ad-'.$creative->slug.'.mp4';
        $relative = $this->builder->relativeProjectDir($creative).'/renders/'.$filename;
        $absolute = base_path($relative);

        File::ensureDirectoryExists(dirname($absolute));

        $result = Process::path($dir)
            ->timeout((int) config('ad_studio.cli.render_timeout'))
            ->run($this->cli(['render', '--output', 'renders/'.$filename]));

        if (! $result->successful()) {
            throw new AdStudioException('Render failed: '.$this->tail($result->errorOutput() ?: $result->output()));
        }

        if (! is_file($absolute)) {
            throw new AdStudioException('The CLI reported success but wrote no MP4. Check the project on disk.');
        }

        return [
            'path' => $relative,
            'bytes' => filesize($absolute),
            'duration' => $this->probeDuration($absolute),
        ];
    }

    /**
     * The quality gate. Warnings are logged and allowed through; errors stop the
     * render, because every error class it reports (runtime exceptions, elements
     * outside their container, contrast below AA) is visible in the output.
     */
    public function check(string $dir): void
    {
        $result = Process::path($dir)
            ->timeout((int) config('ad_studio.cli.check_timeout'))
            ->run($this->cli(['check']));

        if (! $result->successful()) {
            throw new AdStudioException('The composition did not pass validation: '.$this->tail($result->output() ?: $result->errorOutput()));
        }

        if (Str::contains($result->output(), 'warning', ignoreCase: true)) {
            Log::info('Ad Studio check passed with warnings', ['dir' => $dir]);
        }
    }

    /**
     * Contact-sheet snapshots for the panel's preview strip. Best-effort: a
     * creative whose snapshots fail is still perfectly renderable, so this never
     * throws into the render path.
     *
     * @return list<string> relative paths
     */
    public function snapshot(AdCreative $creative, array $times): array
    {
        $dir = $this->builder->projectDir($creative);

        File::deleteDirectory("$dir/snapshots");

        $result = Process::path($dir)
            ->timeout((int) config('ad_studio.cli.check_timeout'))
            ->run($this->cli([
                'snapshot',
                '--at', implode(',', array_map(fn ($t) => number_format($t, 2, '.', ''), $times)),
                '--no-end',
                // The CLI runs Gemini vision analysis by default when a key is in
                // the environment. The panel does not use it, so do not spend it.
                '--describe', 'false',
            ]));

        if (! $result->successful()) {
            Log::warning('Ad Studio snapshot failed', ['creative' => $creative->id, 'error' => $this->tail($result->errorOutput())]);

            return [];
        }

        $relativeDir = $this->builder->relativeProjectDir($creative).'/snapshots';

        return collect(File::glob("$dir/snapshots/frame-*.png"))
            ->sort()
            ->map(fn ($p) => $relativeDir.'/'.basename($p))
            ->values()
            ->all();
    }

    /** The pinned CLI, so a creative re-renders identically over time. */
    private function cli(array $args): array
    {
        return array_merge([
            config('ad_studio.cli.npx'),
            '--yes',
            'hyperframes@'.config('ad_studio.cli.version'),
        ], $args);
    }

    private function probeDuration(string $file): ?float
    {
        $result = Process::timeout(60)->run([
            config('ad_studio.ffprobe'),
            '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=nw=1:nk=1',
            $file,
        ]);

        return $result->successful() ? round((float) trim($result->output()), 2) : null;
    }

    /**
     * CLI output is long and the useful part is at the end; the panel shows this
     * verbatim to an admin, so keep it readable rather than complete.
     */
    private function tail(string $output): string
    {
        $lines = collect(preg_split('/\R/', trim($output)))
            ->reject(fn ($l) => trim($l) === '' || Str::startsWith(trim($l), ['[INFO]', 'npm warn']))
            ->take(-12)
            ->implode("\n");

        return Str::limit($lines, 2000);
    }
}
