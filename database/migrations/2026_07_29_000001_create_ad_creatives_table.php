<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * One row per ad creative authored in the admin Ad Studio.
     *
     * The row owns the *intent* (which template, what copy, which options); the
     * rendered artefact lives on disk in a generated HyperFrames project under
     * hyperframes/videos/. `project_dir` and `render_path` are the pointers to
     * it. Nothing here duplicates what the project files already say — a
     * creative can always be rebuilt from `config`.
     */
    public function up(): void
    {
        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('template', 64);

            // draft → voicing → built → queued → rendering → rendered | failed
            //
            // `voicing` and `queued` are both "waiting for the drainer"; the
            // scheduled ads:process-queue command claims whichever is oldest.
            $table->string('status', 24)->default('draft')->index();

            // Set when the operator asked for one action ("build and render")
            // rather than two: the drainer moves straight from built to queued
            // without a second trip through the panel.
            $table->boolean('auto_render')->default(false);

            // The editable intent: script lines + template options (intervals,
            // screens, palette, voice). Everything the builder needs.
            $table->json('config');

            // Measured Gemini TTS durations per line, keyed by line key. Written
            // by AdVoiceoverService; the timing planner reads it.
            $table->json('vo_manifest')->nullable();

            // Computed frame windows + audio cue times. Stored so the show page
            // can display the cut without re-planning, and so a render is
            // reproducible from the row alone.
            $table->json('timings')->nullable();

            $table->string('project_dir')->nullable();
            $table->string('render_path')->nullable();
            $table->unsignedBigInteger('render_bytes')->nullable();
            $table->decimal('duration_seconds', 6, 2)->nullable();

            // Last failure, surfaced in the panel rather than only in the log.
            $table->text('error')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('render_started_at')->nullable();
            $table->timestamp('rendered_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // The drainer's claim query: oldest pending work first.
            $table->index(['status', 'queued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_creatives');
    }
};
