<?php

namespace App\Livewire\Admin;

use App\Models\AdCreative;
use App\Services\AdStudio\AdCreativeService;
use App\Services\AdStudio\AdStudioException;
use App\Services\AdStudio\AdTemplateRegistry;
use App\Services\AdStudio\AdTimingPlanner;
use App\Services\AdStudio\AdVoiceoverService;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The Ad Studio editor.
 *
 * Livewire rather than a plain form for one reason: a build takes ~30s and a
 * render takes minutes, both in a background drainer, so the page has to poll
 * and update itself. Everything else here is an ordinary validated form.
 *
 * The component never touches the filesystem or the TTS API directly — it edits
 * `config` and asks AdCreativeService for a transition.
 */
class AdCreativeEditor extends Component
{
    public AdCreative $creative;

    /** @var array<string, string> line key => spoken text */
    public array $lines = [];

    /** @var array<string, mixed> option key => value */
    public array $options = [];

    public bool $showAdvanced = false;

    public function mount(AdCreative $creative): void
    {
        $this->creative = $creative;
        $this->hydrateFromConfig();
    }

    #[Computed]
    public function template(): array
    {
        return app(AdTemplateRegistry::class)->get($this->creative->template);
    }

    #[Computed]
    public function registry(): AdTemplateRegistry
    {
        return app(AdTemplateRegistry::class);
    }

    #[Computed]
    public function voiceConfigured(): bool
    {
        return app(AdVoiceoverService::class)->isConfigured();
    }

    /** Option definitions grouped for layout, in the registry's declared order. */
    #[Computed]
    public function optionGroups(): array
    {
        $groups = [];

        foreach ($this->template()['options'] as $key => $definition) {
            $groups[$definition['group']][$key] = $definition;
        }

        return $groups;
    }

    /**
     * Roughly how long the narration will run, before it is measured.
     *
     * Deliberately labelled as an estimate in the UI: the real number comes from
     * ffprobe after synthesis, and this is only here so an operator can see a
     * script drifting long before spending API calls on it. ~2.6 words/second is
     * where the shipped variants landed with this voice and direction.
     */
    #[Computed]
    public function estimatedSeconds(): float
    {
        $words = collect($this->lines)->sum(fn ($line) => count(preg_split('/\s+/', trim((string) $line)) ?: []));

        return round($words / 2.6, 1);
    }

    /**
     * How much narration the template can actually carry — well under its target
     * duration, because the quiz tones and countdowns spend seconds outside the
     * voice. Comparing the estimate against the target instead would tell an
     * operator a 29s script was fine when it is several seconds too long.
     */
    #[Computed]
    public function narrationBudget(): float
    {
        return app(AdTimingPlanner::class)->narrationBudget($this->template());
    }

    #[Computed]
    public function snapshots(): array
    {
        if (! $this->creative->project_dir) {
            return [];
        }

        return collect(File::glob($this->creative->absoluteProjectDir().'/snapshots/frame-*.png'))
            ->sort()
            ->map(fn ($p) => basename($p))
            ->values()
            ->all();
    }

    public function save(): void
    {
        if ($this->isBusy()) {
            return;
        }

        $this->validate($this->rules(), [], $this->attributes());

        $this->creative->forceFill([
            'config' => ['lines' => $this->lines, 'options' => $this->options],
            // Any edit invalidates a finished render: the MP4 on disk no longer
            // matches the config. The row keeps its render_path so the old file
            // stays downloadable, but the status makes the mismatch obvious.
            'status' => $this->creative->status === AdCreative::STATUS_RENDERED
                ? AdCreative::STATUS_DRAFT
                : $this->creative->status,
        ])->save();

        $this->dispatch('saved');
        session()->flash('success', 'Saved. Build to regenerate the narration and the composition.');
    }

    public function build(bool $thenRender = false): void
    {
        if ($this->isBusy()) {
            return;
        }

        if (! $this->voiceConfigured()) {
            session()->flash('error', 'No GEMINI_API_KEY is configured, so narration cannot be generated.');

            return;
        }

        $this->save();

        try {
            app(AdCreativeService::class)->requestBuild($this->creative, $thenRender);
        } catch (AdStudioException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->creative->refresh();
        session()->flash('success', $thenRender
            ? 'Queued: narration, then composition, then render. This takes a few minutes.'
            : 'Queued for narration and build — about half a minute.');
    }

    public function queueRender(): void
    {
        if ($this->isBusy()) {
            return;
        }

        try {
            app(AdCreativeService::class)->queueRender($this->creative);
        } catch (AdStudioException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->creative->refresh();
        session()->flash('success', 'Queued for render — roughly three minutes for a 30s vertical cut.');
    }

    /** Reset every field to the shipped variant's copy. */
    public function resetToTemplate(): void
    {
        if ($this->isBusy()) {
            return;
        }

        $this->creative->forceFill([
            'config' => $this->registry()->defaultConfig($this->creative->template),
        ])->save();

        $this->hydrateFromConfig();
        session()->flash('success', 'Reset to the shipped template copy.');
    }

    /** Polled while work is pending, so the page reflects the drainer. */
    public function poll(): void
    {
        $this->creative->refresh();
    }

    public function render()
    {
        return view('livewire.admin.ad-creative-editor');
    }

    private function hydrateFromConfig(): void
    {
        $config = $this->creative->config;
        $template = $this->template();

        foreach ($template['lines'] as $line) {
            $this->lines[$line['key']] = (string) ($config['lines'][$line['key']] ?? $line['default']);
        }

        foreach ($template['options'] as $key => $definition) {
            $this->options[$key] = $config['options'][$key] ?? $definition['default'];
        }
    }

    /**
     * Validation is generated from the template definition, so adding an option
     * to the registry does not mean remembering to add a rule here too.
     */
    private function rules(): array
    {
        $template = $this->template();
        $rules = [];

        foreach ($template['lines'] as $line) {
            $rules["lines.{$line['key']}"] = ['required', 'string', 'max:'.$line['max']];
        }

        foreach ($template['options'] as $key => $definition) {
            $rules["options.$key"] = match ($definition['type']) {
                'interval' => ['required', 'string', 'in:'.implode(',', array_keys(AdTemplateRegistry::INTERVALS))],
                'shot' => ['required', 'string', 'in:'.implode(',', array_keys(AdTemplateRegistry::SHOTS))],
                'shots' => ['required', 'array', 'min:1', 'max:'.($definition['max_items'] ?? 3)],
                'csv' => ['required', 'string', 'max:120'],
                'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
                'voice' => ['required', 'string', 'in:'.implode(',', array_keys(config('ad_studio.tts.voices')))],
                'textarea' => ['required', 'string', 'max:'.($definition['max'] ?? 600)],
                // Asides and other optional copy are allowed to be blank — the
                // stubs hide the element rather than reserving empty space.
                default => [$this->optional($key) ? 'nullable' : 'required', 'string', 'max:'.($definition['max'] ?? 80)],
            };

            if ($definition['type'] === 'shots') {
                $rules["options.$key.*"] = ['string', 'in:'.implode(',', array_keys(AdTemplateRegistry::SHOTS))];
            }
        }

        return $rules;
    }

    private function optional(string $key): bool
    {
        return str_contains($key, 'aside') || $key === 'cta_note';
    }

    private function attributes(): array
    {
        $attributes = [];

        foreach ($this->template()['lines'] as $line) {
            $attributes["lines.{$line['key']}"] = $line['label'].' line';
        }

        foreach ($this->template()['options'] as $key => $definition) {
            $attributes["options.$key"] = $definition['label'];
        }

        return $attributes;
    }

    /**
     * Every mutating action re-reads the row first: the drainer may have claimed
     * this creative since the page rendered, and writing config underneath a
     * running build would produce a project that matches neither state.
     */
    private function isBusy(): bool
    {
        $this->creative->refresh();

        if ($this->creative->isBusy()) {
            session()->flash('error', 'This creative is being processed right now — wait for it to finish.');

            return true;
        }

        return false;
    }
}
