<?php

namespace Tests\Feature;

use App\Models\TeacherAppointment;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\ZoomHost;
use App\Models\ZoomMeeting;
use App\Services\Teacher\TeacherSchedulingService;
use App\Services\Zoom\ZoomClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Live-lesson coverage. The Zoom REST client is replaced with an in-memory fake
 * bound into the container — the same trick the Stripe tests use — so nothing
 * here touches the network.
 */
class ZoomLessonRoomTest extends TestCase
{
    use RefreshDatabase;

    private FakeZoomClient $zoom;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        config([
            'zoom.enabled' => true,
            'services.zoom.account_id' => 'acct',
            'services.zoom.client_id' => 'cid',
            'services.zoom.client_secret' => 'secret',
            'services.zoom.sdk_key' => 'sdk-key',
            'services.zoom.sdk_secret' => 'sdk-secret',
        ]);

        $this->zoom = new FakeZoomClient;
        $this->app->instance(ZoomClient::class, $this->zoom);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function premiumTeacher(): User
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $teacher->id,
            'slug' => 'teacher-'.$teacher->id,
            'tier' => TeacherProfile::TIER_PREMIUM,
            'status' => TeacherProfile::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        return $teacher->fresh();
    }

    private function host(string $zoomUserId = 'host-1'): ZoomHost
    {
        return ZoomHost::create([
            'zoom_user_id' => $zoomUserId,
            'email' => $zoomUserId.'@harmoniva.app',
            'display_name' => $zoomUserId,
            'is_active' => true,
        ]);
    }

    private function appointment(User $teacher, User $student, ?string $startsAt = null): TeacherAppointment
    {
        $start = $startsAt ? now()->parse($startsAt) : now()->addHour();

        return TeacherAppointment::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(45),
            'status' => TeacherAppointment::STATUS_PENDING,
            'timezone' => 'UTC',
        ]);
    }

    private function scheduling(): TeacherSchedulingService
    {
        return app(TeacherSchedulingService::class);
    }

    // ── Meeting lifecycle ────────────────────────────────────────────────────

    public function test_confirming_a_lesson_creates_a_zoom_meeting(): void
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $appointment = $this->appointment($teacher, $student);

        $this->scheduling()->confirm($appointment, $teacher);

        $appointment->refresh();
        $this->assertSame('zoom', $appointment->meeting_provider);
        $this->assertNotNull($appointment->meeting_url);

        $meeting = $appointment->zoomMeeting;
        $this->assertNotNull($meeting);
        $this->assertSame(ZoomMeeting::STATUS_ACTIVE, $meeting->status);
        $this->assertSame($appointment->meeting_url, $meeting->join_url);
        $this->assertCount(1, $this->zoom->created);
    }

    public function test_a_teacher_supplied_link_wins_over_zoom(): void
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher, 'https://meet.example.com/abc');

        $appointment->refresh();
        $this->assertSame('manual', $appointment->meeting_provider);
        $this->assertSame('https://meet.example.com/abc', $appointment->meeting_url);
        $this->assertNull($appointment->zoomMeeting);
        $this->assertEmpty($this->zoom->created);
    }

    public function test_cancelling_deletes_the_meeting_and_frees_the_host(): void
    {
        $host = $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);
        $meetingId = $appointment->fresh()->zoomMeeting->zoom_meeting_id;

        $this->scheduling()->cancel($appointment, $teacher);

        $this->assertContains($meetingId, $this->zoom->deleted);
        $this->assertSame(ZoomMeeting::STATUS_CANCELLED, ZoomMeeting::first()->status);
        $this->assertNull($appointment->fresh()->meeting_url);

        // The licence is available again for an overlapping lesson.
        $second = $this->appointment($this->premiumTeacher(), User::factory()->create());
        $this->scheduling()->confirm($second, $second->teacher);

        $this->assertSame($host->id, $second->fresh()->zoomMeeting->zoom_host_id);
    }

    public function test_completing_a_lesson_releases_the_host(): void
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);
        $this->scheduling()->complete($appointment, $teacher);

        $this->assertSame(ZoomMeeting::STATUS_CANCELLED, ZoomMeeting::first()->status);
    }

    public function test_rescheduling_moves_the_meeting(): void
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);
        $newStart = now()->addDays(2)->startOfHour()->toImmutable();
        $this->scheduling()->reschedule($appointment, $teacher, $newStart);

        $meeting = $appointment->fresh()->zoomMeeting;
        $this->assertSame($newStart->utc()->format('Y-m-d H:i'), $meeting->starts_at->utc()->format('Y-m-d H:i'));
        $this->assertNotEmpty($this->zoom->updated);
    }

    public function test_rescheduling_onto_a_busy_host_moves_to_a_free_licence(): void
    {
        $first = $this->host('host-1');
        $second = $this->host('host-2');

        $slot = now()->addDays(3)->startOfHour();

        // host-1 is taken at the target time by somebody else's lesson.
        $blocking = $this->appointment($this->premiumTeacher(), User::factory()->create(), $slot->toDateTimeString());
        $this->scheduling()->confirm($blocking, $blocking->teacher);
        $this->assertSame($first->id, $blocking->fresh()->zoomMeeting->zoom_host_id);

        // A lesson elsewhere in the week, then moved on top of that slot.
        $moving = $this->appointment($this->premiumTeacher(), User::factory()->create(), now()->addDays(5)->toDateTimeString());
        $this->scheduling()->confirm($moving, $moving->teacher);
        $this->scheduling()->reschedule($moving, $moving->teacher, $slot->toImmutable());

        $this->assertSame($second->id, $moving->fresh()->zoomMeeting->zoom_host_id);
    }

    // ── Host pool ────────────────────────────────────────────────────────────

    public function test_one_host_is_never_double_booked(): void
    {
        $this->host();
        $slot = now()->addDay()->startOfHour();

        $first = $this->appointment($this->premiumTeacher(), User::factory()->create(), $slot->toDateTimeString());
        $second = $this->appointment($this->premiumTeacher(), User::factory()->create(), $slot->copy()->addMinutes(10)->toDateTimeString());

        $this->scheduling()->confirm($first, $first->teacher);
        $this->scheduling()->confirm($second, $second->teacher);

        $this->assertSame('zoom', $first->fresh()->meeting_provider);
        $this->assertSame('manual', $second->fresh()->meeting_provider);
        $this->assertSame(1, ZoomMeeting::active()->count());
    }

    public function test_an_exhausted_pool_still_confirms_the_lesson(): void
    {
        // No hosts at all.
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);

        $appointment->refresh();
        $this->assertSame(TeacherAppointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame('manual', $appointment->meeting_provider);
        $this->assertNull($appointment->zoomMeeting);
    }

    public function test_zoom_disabled_falls_back_to_manual(): void
    {
        config(['zoom.enabled' => false]);
        $this->host();

        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);

        $this->assertSame('manual', $appointment->fresh()->meeting_provider);
        $this->assertEmpty($this->zoom->created);
    }

    public function test_a_zoom_outage_does_not_block_confirming(): void
    {
        $this->host();
        $this->zoom->failCreate = true;

        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->scheduling()->confirm($appointment, $teacher);

        $appointment->refresh();
        $this->assertSame(TeacherAppointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame('manual', $appointment->meeting_provider);
    }

    // ── Lesson Room access ───────────────────────────────────────────────────

    private function openLesson(): TeacherAppointment
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create(), now()->addMinutes(5)->toDateTimeString());
        $this->scheduling()->confirm($appointment, $teacher);

        return $appointment->fresh();
    }

    public function test_both_parties_can_open_the_room(): void
    {
        $appointment = $this->openLesson();

        $this->actingAs($appointment->teacher)->get(route('lessons.room', $appointment))->assertOk();
        $this->actingAs($appointment->student)->get(route('lessons.room', $appointment))->assertOk();
    }

    public function test_a_third_party_is_refused(): void
    {
        $appointment = $this->openLesson();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('lessons.room', $appointment))->assertForbidden();
        $this->actingAs($stranger)->post(route('lessons.room.signature', $appointment))->assertForbidden();
    }

    public function test_only_the_teacher_receives_a_zak(): void
    {
        $appointment = $this->openLesson();

        $teacherPayload = $this->actingAs($appointment->teacher)
            ->post(route('lessons.room.signature', $appointment))
            ->assertOk()
            ->json();

        $studentPayload = $this->actingAs($appointment->student)
            ->post(route('lessons.room.signature', $appointment))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('zak', $teacherPayload);
        $this->assertArrayNotHasKey('zak', $studentPayload);

        // The SDK secret must never travel to the browser.
        $this->assertSame('sdk-key', $teacherPayload['sdkKey']);
        $this->assertStringNotContainsString('sdk-secret', json_encode($teacherPayload));
    }

    public function test_the_signature_is_refused_outside_the_join_window(): void
    {
        $this->host();
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create(), now()->addDays(2)->toDateTimeString());
        $this->scheduling()->confirm($appointment, $teacher);

        $this->actingAs($teacher)
            ->post(route('lessons.room.signature', $appointment))
            ->assertForbidden();

        // The page itself still renders, showing the "not open yet" state.
        $this->actingAs($teacher)
            ->get(route('lessons.room', $appointment))
            ->assertOk()
            ->assertSee(__('teacher.lesson_room.not_open'));
    }

    public function test_an_unconfirmed_lesson_has_no_room(): void
    {
        $teacher = $this->premiumTeacher();
        $appointment = $this->appointment($teacher, User::factory()->create());

        $this->actingAs($teacher)->get(route('lessons.room', $appointment))->assertNotFound();
    }

    // ── Admin host pool ──────────────────────────────────────────────────────

    public function test_admin_can_view_and_pause_the_host_pool(): void
    {
        $host = $this->host();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.zoom.index'))
            ->assertOk()
            ->assertSee($host->email);

        $this->actingAs($admin)->post(route('admin.zoom.toggle', $host))->assertRedirect();
        $this->assertFalse($host->fresh()->is_active);
    }

    public function test_the_host_pool_is_admin_only(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.zoom.index'))
            ->assertForbidden();
    }

    public function test_sync_command_mirrors_licensed_users(): void
    {
        $this->artisan('zoom:sync-hosts')->assertSuccessful();

        $this->assertDatabaseHas('zoom_hosts', ['zoom_user_id' => 'host-1', 'is_active' => true]);

        // A host Zoom no longer reports stops being handed out, but is kept.
        $stale = $this->host('gone');
        $this->artisan('zoom:sync-hosts')->assertSuccessful();
        $this->assertFalse($stale->fresh()->is_active);
    }
}

/**
 * In-memory stand-in for the Zoom REST API.
 */
class FakeZoomClient extends ZoomClient
{
    public array $created = [];

    public array $updated = [];

    public array $deleted = [];

    public bool $failCreate = false;

    private int $sequence = 0;

    public function configured(): bool
    {
        return true;
    }

    public function accessToken(): string
    {
        return 'fake-token';
    }

    public function createMeeting(string $zoomUserId, array $attributes): array
    {
        if ($this->failCreate) {
            throw new \RuntimeException('Zoom is down.');
        }

        $id = (string) (900000000 + ++$this->sequence);
        $this->created[] = ['host' => $zoomUserId, 'id' => $id] + $attributes;

        return [
            'id' => $id,
            'uuid' => 'uuid-'.$id,
            'join_url' => 'https://zoom.us/j/'.$id,
            'password' => 'pw'.$id,
        ];
    }

    public function updateMeeting(string $meetingId, array $attributes): void
    {
        $this->updated[] = ['id' => $meetingId] + $attributes;
    }

    public function deleteMeeting(string $meetingId): void
    {
        $this->deleted[] = $meetingId;
    }

    public function listLicensedUsers(): array
    {
        return [['id' => 'host-1', 'email' => 'host-1@harmoniva.app', 'name' => 'Classroom 1']];
    }

    public function zakFor(string $zoomUserId): string
    {
        return 'zak-'.$zoomUserId;
    }
}
