<?php

namespace App\Services\AdStudio;

use App\Models\AdCreative;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * The Ad Studio's use cases, in one place, so the Livewire editor, the render
 * drainer and any future command all drive the same state machine:
 *
 *   draft ──build──▶ voicing ──▶ built ──queue──▶ queued ──drainer──▶ rendering
 *                                                                   ├─▶ rendered
 *                                                                   └─▶ failed
 *
 * Every transition that can fail records the message on the row. An admin should
 * be able to see why something broke without opening a log file.
 */
class AdCreativeService
{
    public function __construct(
        private readonly AdTemplateRegistry $registry,
        private readonly AdVoiceoverService $voice,
        private readonly AdProjectBuilder $builder,
        private readonly AdRenderService $renderer,
    ) {}

    /** A new creative starts as an exact copy of the shipped variant's copy. */
    public function create(string $name, string $template, ?User $author = null): AdCreative
    {
        $this->registry->get($template);

        return AdCreative::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'template' => $template,
            'status' => AdCreative::STATUS_DRAFT,
            'config' => $this->registry->defaultConfig($template),
            'created_by' => $author?->id,
        ]);
    }

    /**
     * Ask for a build. The work itself happens in the scheduled drainer.
     *
     * Ten Gemini takes plus a tone synthesis run take roughly half a minute —
     * survivable in a web request on a good day, and a gateway timeout on a bad
     * one. Queueing it keeps the panel responsive and makes a slow API Google's
     * problem rather than the operator's.
     */
    public function requestBuild(AdCreative $creative, bool $thenRender = false): AdCreative
    {
        $creative->forceFill([
            'status' => AdCreative::STATUS_VOICING,
            'auto_render' => $thenRender,
            'queued_at' => now(),
            'error' => null,
        ])->save();

        return $creative;
    }

    /**
     * Synthesize the narration, then build the project.
     *
     * These are one step on purpose: the frame windows are cut to the measured
     * take durations, so a build without fresh measurements would plan against
     * stale numbers and drift out of its target length.
     */
    public function buildWithVoice(AdCreative $creative): AdCreative
    {
        $creative->forceFill([
            'status' => AdCreative::STATUS_VOICING,
            'error' => null,
        ])->save();

        try {
            $template = $this->registry->get($creative->template);

            $vo = $this->voice->synthesizeAll(
                $this->orderedLines($creative, $template),
                (string) $creative->option('voice', 'Kore'),
                (string) $creative->option('voice_direction', ''),
            );

            $plan = $this->builder->build($creative, $vo);

            $creative->forceFill([
                'status' => AdCreative::STATUS_BUILT,
                'vo_manifest' => $this->publicManifest($vo),
                'timings' => $this->publicTimings($plan),
                'project_dir' => $this->builder->relativeProjectDir($creative),
                'duration_seconds' => $plan['total'],
                'error' => null,
            ])->save();
        } catch (Throwable $e) {
            $this->fail($creative, $e, 'build');
        }

        return $creative->refresh();
    }

    public function queueRender(AdCreative $creative): AdCreative
    {
        if (! $creative->isRenderable()) {
            throw new AdStudioException('This creative has not been built yet, so there is nothing to render.');
        }

        $creative->forceFill([
            'status' => AdCreative::STATUS_QUEUED,
            'queued_at' => now(),
            'error' => null,
        ])->save();

        return $creative;
    }

    /**
     * Claim and process the oldest piece of pending work — a build or a render.
     *
     * The claim is a locked read plus an immediate status write inside a
     * transaction, so two overlapping drainer runs cannot both pick up the same
     * row: `withoutOverlapping()` on the schedule is a lock with an expiry, not
     * a guarantee, and a render outliving that expiry is exactly the case where
     * a double-claim would happen.
     */
    public function processNext(): ?AdCreative
    {
        /** @var array{0: ?AdCreative, 1: ?string} $claim */
        $claim = DB::transaction(function () {
            $next = AdCreative::query()
                ->whereIn('status', [AdCreative::STATUS_VOICING, AdCreative::STATUS_QUEUED])
                ->orderBy('queued_at')
                ->lockForUpdate()
                ->first();

            if (! $next) {
                return [null, null];
            }

            // Remember which stage was claimed BEFORE overwriting the status —
            // the status is both the work item and the progress indicator, so it
            // is the only record of what this run is supposed to do.
            $stage = $next->status === AdCreative::STATUS_QUEUED ? 'render' : 'build';

            // Claim by moving the row out of the pending states inside the same
            // transaction as the locked read, so an overlapping drainer run
            // cannot pick it up a second time.
            $next->forceFill($stage === 'render'
                ? ['status' => AdCreative::STATUS_RENDERING, 'render_started_at' => now()]
                : ['status' => AdCreative::STATUS_VOICING])->save();

            return [$next, $stage];
        });

        [$creative, $stage] = $claim;

        if (! $creative) {
            return null;
        }

        if ($stage === 'build') {
            $creative = $this->buildWithVoice($creative);

            // "Build and render" is one operator action; chain into the render
            // rather than making them come back to the panel to press again.
            if ($creative->status === AdCreative::STATUS_BUILT && $creative->auto_render) {
                $this->queueRender($creative);
            }

            return $creative->refresh();
        }

        try {
            $result = $this->renderer->render($creative);

            $creative->forceFill([
                'status' => AdCreative::STATUS_RENDERED,
                'render_path' => $result['path'],
                'render_bytes' => $result['bytes'],
                'duration_seconds' => $result['duration'] ?? $creative->duration_seconds,
                'rendered_at' => now(),
                'error' => null,
            ])->save();

            // Best-effort preview strip: one frame per beat, so the panel can
            // show the cut without anyone downloading the MP4.
            $this->renderer->snapshot($creative, $this->snapshotTimes($creative));
        } catch (Throwable $e) {
            $this->fail($creative, $e, 'render');
        }

        return $creative->refresh();
    }

    /**
     * Delete the row and its generated project. The project is disposable — the
     * row is what a creative actually is — but leaving orphaned directories
     * around would slowly fill the disk with 24 MB of copied fonts apiece.
     */
    public function delete(AdCreative $creative): void
    {
        $dir = $this->builder->projectDir($creative);

        if (is_dir($dir) && Str::startsWith($dir, base_path(config('ad_studio.projects_root')).'/ad-')) {
            File::deleteDirectory($dir);
        }

        $creative->delete();
    }

    /** One representative frame per beat, at roughly two-thirds through it. */
    public function snapshotTimes(AdCreative $creative): array
    {
        $frames = $creative->timings['frames'] ?? [];

        if ($frames === []) {
            return [0.0, 5.0, 10.0, 15.0, 20.0, 25.0];
        }

        return array_map(
            fn ($f) => round($f['start'] + $f['duration'] * 0.66, 2),
            $frames
        );
    }

    /**
     * Script lines in template order. The planner and the generated SCRIPT.md
     * both depend on this order, so it comes from the template rather than from
     * however the JSON column happened to serialize.
     */
    private function orderedLines(AdCreative $creative, array $template): array
    {
        $lines = $creative->scriptLines();
        $ordered = [];

        foreach ($template['lines'] as $line) {
            $ordered[$line['key']] = trim((string) ($lines[$line['key']] ?? $line['default']));
        }

        return $ordered;
    }

    /** Strip absolute cache paths — the row is shown in a browser. */
    private function publicManifest(array $vo): array
    {
        return collect($vo)->map(fn ($take) => [
            'text' => $take['text'],
            'voice' => $take['voice'],
            'seconds' => $take['seconds'],
        ])->all();
    }

    /** Only what the panel renders: the cut, not the planner's scratch values. */
    private function publicTimings(array $plan): array
    {
        return [
            'total' => $plan['total'],
            'natural' => $plan['natural'],
            'warnings' => $plan['warnings'],
            'frames' => collect($plan['frames'])->map(fn ($f) => [
                'id' => $f['id'],
                'kind' => $f['kind'],
                'line' => $f['line'],
                'start' => $f['start'],
                'duration' => $f['duration'],
            ])->all(),
            'audio' => collect($plan['audio'])->map(fn ($a) => [
                'id' => $a['id'],
                'label' => $a['label'],
                'start' => $a['start'],
                'duration' => $a['duration'],
                'track' => $a['track'],
            ])->all(),
        ];
    }

    private function fail(AdCreative $creative, Throwable $e, string $stage): void
    {
        // AdStudioException messages are written for an admin to read verbatim.
        // Anything else is a bug, and its raw message would be noise in the UI.
        $message = $e instanceof AdStudioException
            ? $e->getMessage()
            : 'Unexpected '.class_basename($e).' during '.$stage.'. Check the application log for details.';

        Log::error('Ad Studio '.$stage.' failed', [
            'creative' => $creative->id,
            'exception' => $e,
        ]);

        $creative->forceFill([
            'status' => AdCreative::STATUS_FAILED,
            'error' => $message,
        ])->save();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'creative';
        $slug = $base;
        $i = 2;

        while (AdCreative::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
