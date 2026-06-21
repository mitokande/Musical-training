<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DevMidiViewerTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_MIDI = "MThd\x00\x00\x00\x06\x00\x00\x00\x01\x00\x60MTrk\x00\x00\x00\x04\x00\xFF\x2F\x00";

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_non_admin_cannot_access_midi_viewer(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/dev/midi')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dev/midi')->assertRedirect('/login');
    }

    public function test_admin_can_view_midi_viewer_page(): void
    {
        $this->actingAs($this->admin())->get('/dev/midi')->assertOk();
    }

    public function test_admin_can_upload_and_view_a_midi_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('My Melody.mid', self::VALID_MIDI);

        $response = $this->actingAs($this->admin())->post('/dev/midi/upload', ['midi' => $file]);

        $response->assertRedirect();

        $stored = Storage::disk('local')->files('dev-midi');
        $this->assertCount(1, $stored);
        $this->assertStringEndsWith('_my-melody.mid', $stored[0]);

        $this->actingAs($this->admin())
            ->get('/dev/midi/file/'.basename($stored[0]))
            ->assertOk()
            ->assertHeader('Content-Type', 'audio/midi');
    }

    public function test_upload_rejects_file_without_mthd_header(): void
    {
        $file = UploadedFile::fake()->createWithContent('fake.mid', 'not a midi file');

        $response = $this->actingAs($this->admin())->post('/dev/midi/upload', ['midi' => $file]);

        $response->assertSessionHasErrors('midi');
        $this->assertEmpty(Storage::disk('local')->files('dev-midi'));
    }

    public function test_upload_rejects_wrong_extension(): void
    {
        $file = UploadedFile::fake()->createWithContent('notes.txt', self::VALID_MIDI);

        $this->actingAs($this->admin())
            ->post('/dev/midi/upload', ['midi' => $file])
            ->assertSessionHasErrors('midi');
    }

    public function test_serving_unknown_file_returns_404(): void
    {
        $this->actingAs($this->admin())->get('/dev/midi/file/nope.mid')->assertNotFound();
    }

    public function test_admin_can_delete_a_midi_file(): void
    {
        Storage::disk('local')->put('dev-midi/test.mid', self::VALID_MIDI);

        $this->actingAs($this->admin())
            ->delete('/dev/midi/test.mid')
            ->assertRedirect(route('dev.midi.index'));

        $this->assertEmpty(Storage::disk('local')->files('dev-midi'));
    }
}
