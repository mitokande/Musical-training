<?php

namespace Tests\Feature;

use App\Models\LiveLesson;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Derslik\LiveLessonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Ad-hoc live lessons on Derslik (meeting.harmoniva.app). Derslik's REST API
 * is faked with Http::fake — nothing here touches the network.
 */
class DerslikLiveLessonTest extends TestCase
{
    use RefreshDatabase;

    private const DERSLIK = 'https://meeting.test';

    private int $rooms = 0;

    /** Flip to simulate a Derslik outage (later Http::fake calls would not override these stubs). */
    private bool $down = false;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.derslik.url' => self::DERSLIK,
            'services.derslik.api_key' => 'dk_test_key',
            'services.derslik.lesson_hours' => 12,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            self::DERSLIK.'/api/v1/rooms' => function (Request $request) {
                if ($this->down) {
                    return Http::response(['error' => ['code' => 'internal_error']], 500);
                }
                $id = 'room'.(++$this->rooms);

                return Http::response([
                    'id' => $id,
                    'title' => $request['title'],
                    'teacherUrl' => self::DERSLIK."/r/{$id}?t=SECRETTEACHERKEY{$this->rooms}",
                    'studentUrl' => self::DERSLIK."/r/{$id}",
                ], 201);
            },
            self::DERSLIK.'/api/v1/rooms/*/end' => fn () => $this->down ? Http::response(null, 503) : Http::response(['ended' => true]),
            self::DERSLIK.'/api/v1/rooms/*' => fn (Request $request) => $this->down ? Http::response(null, 503) : ($request->method() === 'DELETE'
                ? Http::response(null, 204)
                : Http::response(['id' => 'x', 'live' => ['active' => true, 'participants' => 2, 'teacherPresent' => true]])),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function teacher(string $tier = TeacherProfile::TIER_PREMIUM): User
    {
        $teacher = User::factory()->create(['role' => 'user', 'name' => 'Ayşe', 'surname' => 'Yılmaz']);
        TeacherProfile::create([
            'user_id' => $teacher->id,
            'slug' => 'teacher-'.$teacher->id,
            'tier' => $tier,
            'status' => TeacherProfile::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        return $teacher->fresh();
    }

    private function startLesson(User $teacher, ?string $title = 'Piyano · Can ile'): LiveLesson
    {
        $this->actingAs($teacher)->post(route('teacher.live-lessons.store'), ['title' => $title]);

        return LiveLesson::where('teacher_id', $teacher->id)->latest('id')->firstOrFail();
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function test_dashboard_shows_the_live_lesson_card_to_a_premium_teacher(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee(__('teacher.live_lessons.start'))
            ->assertSee(route('teacher.live-lessons.store'), false);
    }

    public function test_the_card_is_hidden_while_derslik_is_not_configured(): void
    {
        config(['services.derslik.api_key' => null]);

        $this->actingAs($this->teacher())
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertDontSee(__('teacher.live_lessons.start'));

        $this->get(route('teacher.live-lessons.index'))->assertNotFound();
    }

    public function test_a_basic_teacher_sees_the_card_locked_and_cannot_start(): void
    {
        $teacher = $this->teacher(TeacherProfile::TIER_BASIC);

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee(__('teacher.live_lessons.locked'))
            ->assertDontSee(route('teacher.live-lessons.store'), false);

        $this->post(route('teacher.live-lessons.store'))->assertForbidden();
        Http::assertNothingSent();
    }

    // ── Starting ─────────────────────────────────────────────────────────────

    public function test_starting_creates_a_derslik_room_and_opens_the_teacher_room(): void
    {
        $teacher = $this->teacher();

        $response = $this->actingAs($teacher)->post(route('teacher.live-lessons.store'), ['title' => 'Piyano · Can ile']);

        $lesson = LiveLesson::firstOrFail();
        $response->assertRedirect(route('live-lessons.room', $lesson));
        $this->assertSame('Piyano · Can ile', $lesson->title);
        $this->assertSame('room1', $lesson->derslik_room_id);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{24}$/', $lesson->share_token);

        Http::assertSent(fn (Request $r) => $r->url() === self::DERSLIK.'/api/v1/rooms'
            && $r->method() === 'POST'
            && $r->hasHeader('Authorization', 'Bearer dk_test_key')
            && $r['title'] === 'Piyano · Can ile'
            && $r['waitingRoom'] === true
            && $r['metadata']['harmonivaTeacherId'] === (string) $teacher->id);

        // The teacher key is a credential: never stored in clear text.
        $raw = DB::table('live_lessons')->value('teacher_url');
        $this->assertStringNotContainsString('SECRETTEACHERKEY', $raw);
        $this->assertStringContainsString('SECRETTEACHERKEY', $lesson->teacher_url);
    }

    public function test_an_empty_title_gets_a_default_with_the_teacher_name(): void
    {
        $lesson = $this->startLesson($this->teacher(), null);

        $this->assertSame(__('teacher.live_lessons.default_title', ['name' => 'Ayşe Yılmaz']), $lesson->title);
    }

    public function test_a_derslik_outage_returns_to_the_form_with_an_error(): void
    {
        $this->down = true;

        $this->actingAs($this->teacher())
            ->from(route('teacher.dashboard'))
            ->post(route('teacher.live-lessons.store'), ['title' => 'Ders'])
            ->assertRedirect(route('teacher.dashboard'))
            ->assertSessionHasErrors(['title' => __('teacher.live_lessons.unavailable')]);

        $this->assertDatabaseCount('live_lessons', 0);
    }

    public function test_a_teacher_cannot_hold_more_than_the_open_lesson_limit(): void
    {
        $teacher = $this->teacher();
        for ($i = 0; $i < LiveLessonService::MAX_OPEN_PER_TEACHER; $i++) {
            $this->startLesson($teacher, "Ders {$i}");
        }

        $this->actingAs($teacher)
            ->post(route('teacher.live-lessons.store'), ['title' => 'Bir tane daha'])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('live_lessons', LiveLessonService::MAX_OPEN_PER_TEACHER);
    }

    public function test_the_title_is_validated(): void
    {
        $this->actingAs($this->teacher())
            ->post(route('teacher.live-lessons.store'), ['title' => str_repeat('x', 81)])
            ->assertSessionHasErrors('title');

        Http::assertNothingSent();
    }

    // ── Teacher room ─────────────────────────────────────────────────────────

    public function test_the_teacher_room_embeds_the_teacher_url_with_embed_and_name(): void
    {
        $teacher = $this->teacher();
        $lesson = $this->startLesson($teacher);

        $this->actingAs($teacher)
            ->get(route('live-lessons.room', $lesson))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee(e(self::DERSLIK.'/r/room1?t=SECRETTEACHERKEY1&embed=1&name=Ay%C5%9Fe%20Y%C4%B1lmaz'), false)
            ->assertSee($lesson->joinUrl())
            ->assertSee(route('teacher.live-lessons.end', $lesson), false);
    }

    public function test_only_the_owner_can_open_the_teacher_room(): void
    {
        $lesson = $this->startLesson($this->teacher());

        $this->actingAs($this->teacher())->get(route('live-lessons.room', $lesson))->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('live-lessons.room', $lesson))->assertNotFound();

        auth()->logout();
        $this->get(route('live-lessons.room', $lesson))->assertRedirect(route('login'));
    }

    // ── Student link ─────────────────────────────────────────────────────────

    public function test_a_guest_can_open_the_student_link_and_never_sees_the_teacher_key(): void
    {
        $lesson = $this->startLesson($this->teacher());
        auth()->logout();

        $this->get(route('live-lessons.join', $lesson->share_token))
            ->assertOk()
            ->assertSee(e(self::DERSLIK.'/r/room1?embed=1'), false)
            ->assertSee('Ayşe Yılmaz')
            ->assertDontSee('SECRETTEACHERKEY');
    }

    public function test_a_signed_in_student_gets_their_name_filled_in(): void
    {
        $lesson = $this->startLesson($this->teacher());
        $student = User::factory()->create(['name' => 'Can', 'surname' => 'Demir']);

        $this->actingAs($student)
            ->get(route('live-lessons.join', $lesson->share_token))
            ->assertOk()
            ->assertSee(e(self::DERSLIK.'/r/room1?embed=1&name=Can%20Demir'), false);
    }

    public function test_the_teacher_opening_their_own_student_link_lands_in_the_teacher_room(): void
    {
        $teacher = $this->teacher();
        $lesson = $this->startLesson($teacher);

        $this->actingAs($teacher)
            ->get(route('live-lessons.join', $lesson->share_token))
            ->assertRedirect(route('live-lessons.room', $lesson));
    }

    public function test_an_unknown_link_is_not_found(): void
    {
        $this->get('/live/'.str_repeat('a', 24))->assertNotFound();
        $this->get('/live/short')->assertNotFound();
    }

    // ── Ending ───────────────────────────────────────────────────────────────

    public function test_ending_disconnects_everyone_deletes_the_room_and_closes_the_link(): void
    {
        $teacher = $this->teacher();
        $lesson = $this->startLesson($teacher);

        $this->actingAs($teacher)
            ->post(route('teacher.live-lessons.end', $lesson))
            ->assertRedirect(route('teacher.dashboard'))
            ->assertSessionHas('status', 'live-lesson-ended');

        $this->assertNotNull($lesson->fresh()->ended_at);
        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r->url() === self::DERSLIK.'/api/v1/rooms/room1/end');
        Http::assertSent(fn (Request $r) => $r->method() === 'DELETE' && $r->url() === self::DERSLIK.'/api/v1/rooms/room1');

        auth()->logout();
        $this->get(route('live-lessons.join', $lesson->share_token))
            ->assertStatus(410)
            ->assertSee(__('teacher.live_lessons.ended_title'));

        $this->actingAs($teacher)
            ->get(route('live-lessons.room', $lesson))
            ->assertRedirect(route('teacher.live-lessons.index'));
    }

    public function test_another_teacher_cannot_end_the_lesson(): void
    {
        $lesson = $this->startLesson($this->teacher());

        $this->actingAs($this->teacher())
            ->post(route('teacher.live-lessons.end', $lesson))
            ->assertNotFound();

        $this->assertNull($lesson->fresh()->ended_at);
    }

    public function test_the_link_closes_even_if_derslik_cannot_delete_the_room(): void
    {
        $teacher = $this->teacher();
        $lesson = $this->startLesson($teacher);
        $this->down = true;

        $this->actingAs($teacher)->post(route('teacher.live-lessons.end', $lesson))->assertRedirect();

        $this->assertNotNull($lesson->fresh()->ended_at);
    }

    // ── Live lessons page ────────────────────────────────────────────────────

    public function test_the_live_lessons_page_lists_open_lessons_with_live_status(): void
    {
        $teacher = $this->teacher();
        $open = $this->startLesson($teacher, 'Açık ders');
        $ended = $this->startLesson($teacher, 'Biten ders');
        $this->post(route('teacher.live-lessons.end', $ended));

        $this->get(route('teacher.live-lessons.index'))
            ->assertOk()
            ->assertSee('Açık ders')
            ->assertSee($open->joinUrl())
            ->assertSee(__('teacher.live_lessons.live_now', ['count' => 2]))
            ->assertSee('Biten ders')
            ->assertDontSee($ended->joinUrl());
    }

    public function test_the_school_panel_runs_the_same_feature(): void
    {
        $this->assertTrue(Route::has('school.live-lessons.store'));
        $this->assertTrue(Route::has('school.live-lessons.index'));
    }

    // ── Cleanup ──────────────────────────────────────────────────────────────

    public function test_stale_lessons_are_closed_and_their_rooms_deleted(): void
    {
        $teacher = $this->teacher();
        $stale = $this->startLesson($teacher, 'Unutulan');
        $fresh = $this->startLesson($teacher, 'Yeni');
        $stale->forceFill(['created_at' => now()->subHours(13)])->save();

        $this->get(route('live-lessons.join', $stale->share_token))->assertStatus(410);

        $this->artisan('derslik:close-stale-lessons')->assertSuccessful();

        $this->assertNotNull($stale->fresh()->ended_at);
        $this->assertNull($fresh->fresh()->ended_at);
        Http::assertSent(fn (Request $r) => $r->method() === 'DELETE' && $r->url() === self::DERSLIK.'/api/v1/rooms/'.$stale->derslik_room_id);
        Http::assertNotSent(fn (Request $r) => $r->method() === 'DELETE' && $r->url() === self::DERSLIK.'/api/v1/rooms/'.$fresh->derslik_room_id);
    }
}
