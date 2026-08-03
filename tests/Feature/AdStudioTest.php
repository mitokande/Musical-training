<?php

namespace Tests\Feature;

use App\Livewire\Admin\AdCreativeEditor;
use App\Models\AdCreative;
use App\Models\User;
use App\Services\AdStudio\AdCreativeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Ad Studio panel.
 *
 * These cover the parts that do not need ffmpeg, node or a network call: access
 * control, the editor's validation and state machine, and serving generated
 * artefacts. Building and rendering are exercised by running the real drainer
 * against a real project — they are not mocked here, because a mocked render
 * would prove nothing about the thing that actually breaks.
 */
class AdStudioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function creative(): AdCreative
    {
        return app(AdCreativeService::class)->create('Test creative', 'tiktok-rounds');
    }

    public function test_the_studio_is_admin_only(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.ad-studio.index'))->assertForbidden();

        $this->actingAs($this->admin())->get(route('admin.ad-studio.index'))->assertOk();
    }

    public function test_the_kill_switch_hides_the_studio_entirely(): void
    {
        config(['ad_studio.enabled' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.ad-studio.index'))
            ->assertNotFound();
    }

    public function test_a_new_creative_starts_as_the_shipped_variants_copy(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.ad-studio.store'), ['name' => 'August test', 'template' => 'tiktok-rounds'])
            ->assertRedirect();

        $creative = AdCreative::firstOrFail();

        $this->assertSame('august-test', $creative->slug);
        $this->assertSame(AdCreative::STATUS_DRAFT, $creative->status);

        // A fresh draft must render the known-good cut before anyone edits a
        // word, so its defaults are the shipped script verbatim.
        $this->assertSame('Three rounds. Most people only get one.', $creative->config['lines']['hook']);
        $this->assertSame('tritone', $creative->config['options']['round3_interval']);
    }

    public function test_slugs_do_not_collide(): void
    {
        $service = app(AdCreativeService::class);

        $this->assertSame('same-name', $service->create('Same name', 'tiktok-rounds')->slug);
        $this->assertSame('same-name-2', $service->create('Same name', 'tiktok-rounds')->slug);
    }

    public function test_the_editor_saves_edited_copy(): void
    {
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('lines.hook', 'Two rounds. Nobody gets both.')
            ->set('options.round1_interval', 'perfect-5th')
            ->call('save')
            ->assertHasNoErrors();

        $creative->refresh();

        $this->assertSame('Two rounds. Nobody gets both.', $creative->config['lines']['hook']);
        $this->assertSame('perfect-5th', $creative->config['options']['round1_interval']);
    }

    public function test_the_editor_rejects_copy_that_cannot_fit_its_frame(): void
    {
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('lines.hook', str_repeat('a very long hook line ', 20))
            ->call('save')
            ->assertHasErrors(['lines.hook']);
    }

    public function test_the_editor_rejects_an_interval_that_has_no_tone(): void
    {
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('options.round2_interval', 'augmented-9th')
            ->call('save')
            ->assertHasErrors(['options.round2_interval']);
    }

    public function test_optional_asides_may_be_left_blank(): void
    {
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('options.answer_aside_2', '')
            ->set('options.cta_note', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('', $creative->fresh()->config['options']['answer_aside_2']);
    }

    public function test_editing_a_rendered_creative_marks_it_stale(): void
    {
        $creative = $this->creative();
        $creative->update(['status' => AdCreative::STATUS_RENDERED]);

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('lines.cta', 'Comment your score. Train free at harmoniva dot app.')
            ->call('save');

        // The MP4 on disk no longer matches the config; the status has to say so
        // or an operator will ship the previous cut believing it is the new one.
        $this->assertSame(AdCreative::STATUS_DRAFT, $creative->fresh()->status);
    }

    public function test_a_busy_creative_cannot_be_edited_underneath_the_drainer(): void
    {
        $creative = $this->creative();
        $original = $creative->config['lines']['hook'];
        $creative->update(['status' => AdCreative::STATUS_RENDERING]);

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->set('lines.hook', 'Changed mid-render')
            ->call('save');

        $this->assertSame($original, $creative->fresh()->config['lines']['hook']);
    }

    public function test_a_draft_cannot_be_queued_for_render(): void
    {
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->call('queueRender');

        $this->assertSame(AdCreative::STATUS_DRAFT, $creative->fresh()->status);
    }

    public function test_build_queues_the_work_rather_than_running_it_in_the_request(): void
    {
        config(['ad_studio.tts.key' => 'test-key']);
        $creative = $this->creative();

        Livewire::actingAs($this->admin())
            ->test(AdCreativeEditor::class, ['creative' => $creative])
            ->call('build', true);

        $creative->refresh();

        $this->assertSame(AdCreative::STATUS_VOICING, $creative->status);
        $this->assertTrue($creative->auto_render);
        $this->assertNotNull($creative->queued_at);
    }

    public function test_the_drainer_claims_the_oldest_pending_creative_once(): void
    {
        $service = app(AdCreativeService::class);

        $first = $service->create('First', 'tiktok-rounds');
        $second = $service->create('Second', 'tiktok-rounds');

        $first->update(['status' => AdCreative::STATUS_QUEUED, 'queued_at' => now()->subMinute()]);
        $second->update(['status' => AdCreative::STATUS_QUEUED, 'queued_at' => now()]);

        // Nothing is built, so the render fails — the point here is only that the
        // oldest row was the one claimed, and that it left the queue.
        $claimed = $service->processNext();

        $this->assertSame($first->id, $claimed->id);
        $this->assertNotSame(AdCreative::STATUS_QUEUED, $claimed->status);
        $this->assertSame(AdCreative::STATUS_QUEUED, $second->fresh()->status);
    }

    public function test_a_failed_build_records_a_message_an_admin_can_act_on(): void
    {
        $creative = $this->creative();
        $creative->update(['status' => AdCreative::STATUS_QUEUED, 'queued_at' => now()]);

        $failed = app(AdCreativeService::class)->processNext();

        $this->assertSame(AdCreative::STATUS_FAILED, $failed->status);
        $this->assertStringContainsString('not been built', $failed->error);
    }

    public function test_snapshot_paths_cannot_escape_the_creatives_own_directory(): void
    {
        $creative = $this->creative();
        $creative->update(['project_dir' => 'hyperframes/videos/ad-'.$creative->slug]);

        foreach (['../../../.env', '..%2F..%2F.env', 'nope.png'] as $attempt) {
            $this->actingAs($this->admin())
                ->get(route('admin.ad-studio.snapshot', [$creative, $attempt]))
                ->assertNotFound();
        }
    }

    public function test_downloads_404_when_there_is_no_render(): void
    {
        $creative = $this->creative();

        $this->actingAs($this->admin())
            ->get(route('admin.ad-studio.download', $creative))
            ->assertNotFound();
    }

    public function test_deleting_a_busy_creative_is_refused(): void
    {
        $creative = $this->creative();
        $creative->update(['status' => AdCreative::STATUS_RENDERING]);

        $this->actingAs($this->admin())
            ->delete(route('admin.ad-studio.destroy', $creative))
            ->assertRedirect();

        $this->assertDatabaseHas('ad_creatives', ['id' => $creative->id]);
    }
}
