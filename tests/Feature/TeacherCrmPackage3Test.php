<?php

namespace Tests\Feature;

use App\Models\TeacherAppointment;
use App\Models\TeacherAvailability;
use App\Models\TeacherBookingSetting;
use App\Models\TeacherConversation;
use App\Models\TeacherConversationAttachment;
use App\Models\TeacherProfile;
use App\Models\TeacherReview;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Notifications\Teacher\AppointmentRequested;
use App\Notifications\Teacher\AppointmentStatusChanged;
use App\Notifications\Teacher\TeacherMessageReceived;
use App\Notifications\Teacher\TeacherReviewReceived;
use App\Services\Teacher\TeacherSchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherCrmPackage3Test extends TestCase
{
    use RefreshDatabase;

    private function premiumTeacher(string $status = TeacherProfile::STATUS_APPROVED): User
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $teacher->id,
            'slug' => 'teacher-'.$teacher->id,
            'tier' => TeacherProfile::TIER_PREMIUM,
            'status' => $status,
            'approved_at' => $status === TeacherProfile::STATUS_APPROVED ? now() : null,
        ]);

        return $teacher->fresh();
    }

    private function basicTeacher(): User
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $teacher->id,
            'slug' => 'basic-'.$teacher->id,
            'tier' => TeacherProfile::TIER_BASIC,
            'status' => TeacherProfile::STATUS_DRAFT,
        ]);

        return $teacher->fresh();
    }

    private function connect(User $teacher, User $student): TeacherStudentRelationship
    {
        return TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);
    }

    // ── Messaging ────────────────────────────────────────────────────────────

    public function test_student_and_premium_teacher_can_exchange_messages(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);

        // Student starts the conversation.
        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}");
        $conversation = TeacherConversation::first();
        $this->assertNotNull($conversation);

        $this->actingAs($student)->post("/teacher-messages/{$conversation->id}", ['body' => 'Hello teacher!'])
            ->assertRedirect(route('teacher-messages.show', $conversation));

        // Teacher replies.
        $this->actingAs($teacher)->post("/teacher/messages/{$conversation->id}", ['body' => 'Hello student!'])
            ->assertRedirect(route('teacher.messages.show', $conversation));

        $this->assertSame(2, $conversation->messages()->count());
        Notification::assertSentTo($teacher, TeacherMessageReceived::class);
        Notification::assertSentTo($student, TeacherMessageReceived::class);
    }

    public function test_basic_teacher_can_read_but_not_reply(): void
    {
        Notification::fake();
        $teacher = $this->basicTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);

        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}");
        $conversation = TeacherConversation::first();
        $this->actingAs($student)->post("/teacher-messages/{$conversation->id}", ['body' => 'Hi!']);

        // Basic teacher can open the thread…
        $this->actingAs($teacher)->get("/teacher/messages/{$conversation->id}")->assertOk();
        // …but cannot send.
        $this->actingAs($teacher)->post("/teacher/messages/{$conversation->id}", ['body' => 'Reply'])
            ->assertSessionHasErrors('body');

        $this->assertSame(1, $conversation->messages()->count());
    }

    public function test_messaging_requires_active_relationship(): void
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();

        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}")
            ->assertSessionHasErrors('conversation');

        $this->assertSame(0, TeacherConversation::count());
    }

    public function test_revoked_relationship_blocks_new_messages(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $rel = $this->connect($teacher, $student);

        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}");
        $conversation = TeacherConversation::first();

        $rel->update(['status' => TeacherStudentRelationship::STATUS_REVOKED_BY_STUDENT]);

        $this->actingAs($student)->post("/teacher-messages/{$conversation->id}", ['body' => 'Hi'])
            ->assertSessionHasErrors('body');
    }

    public function test_attachment_is_stored_privately_and_download_is_authorized(): void
    {
        Notification::fake();
        Storage::fake('local');
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);

        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}");
        $conversation = TeacherConversation::first();

        $this->actingAs($student)->post("/teacher-messages/{$conversation->id}", [
            'body' => 'See attachment',
            'attachments' => [UploadedFile::fake()->create('homework.pdf', 100, 'application/pdf')],
        ]);

        $attachment = TeacherConversationAttachment::first();
        $this->assertNotNull($attachment);
        Storage::disk('local')->assertExists($attachment->path);

        // Participants may download; strangers may not.
        $this->actingAs($teacher)->get("/teacher-message-attachments/{$attachment->id}")->assertOk();
        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get("/teacher-message-attachments/{$attachment->id}")->assertForbidden();
    }

    public function test_stranger_cannot_read_conversation(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);
        $this->actingAs($student)->post("/teacher-messages/start/{$teacher->id}");
        $conversation = TeacherConversation::first();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get("/teacher-messages/{$conversation->id}")->assertForbidden();
    }

    // ── Availability & booking ───────────────────────────────────────────────

    private function openBooking(User $teacher): void
    {
        TeacherBookingSetting::forTeacher($teacher->id)->update([
            'booking_enabled' => true,
            'lesson_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'advance_booking_days' => 30,
            'min_notice_hours' => 0,
            'timezone' => 'UTC',
        ]);

        // Available every day 09:00–17:00.
        foreach (range(0, 6) as $weekday) {
            TeacherAvailability::create([
                'teacher_id' => $teacher->id,
                'weekday' => $weekday,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);
        }
    }

    public function test_slots_endpoint_returns_bookable_times(): void
    {
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();

        $date = CarbonImmutable::now('UTC')->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($student)
            ->getJson("/teachers/teacher-{$teacher->id}/slots?date={$date}");

        $response->assertOk();
        $this->assertNotEmpty($response->json('slots'));
        $this->assertSame('09:00', $response->json('slots.0.label'));
    }

    public function test_student_books_and_teacher_confirms_with_meeting_link(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();

        $startsAt = CarbonImmutable::now('UTC')->addDays(3)->setTime(10, 0);

        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/book", [
            'starts_at' => $startsAt->toIso8601String(),
            'topic' => 'First lesson',
        ])->assertRedirect(route('my-appointments.index'));

        $appointment = TeacherAppointment::first();
        $this->assertSame(TeacherAppointment::STATUS_PENDING, $appointment->status);
        Notification::assertSentTo($teacher, AppointmentRequested::class);

        $this->actingAs($teacher)->post("/teacher/appointments/{$appointment->id}/confirm", [
            'meeting_url' => 'https://meet.example.com/lesson-1',
        ])->assertSessionHas('status', 'appointment-confirmed');

        $appointment->refresh();
        $this->assertTrue($appointment->isConfirmed());
        $this->assertSame('https://meet.example.com/lesson-1', $appointment->meeting_url);
        Notification::assertSentTo($student, AppointmentStatusChanged::class);
    }

    public function test_confirmed_slot_is_no_longer_offered(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();
        $other = User::factory()->create();

        $startsAt = CarbonImmutable::now('UTC')->addDays(3)->setTime(9, 0);

        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/book", [
            'starts_at' => $startsAt->toIso8601String(),
        ]);

        // Second student cannot book the same slot (pending blocks it too).
        $this->actingAs($other)->post("/teachers/teacher-{$teacher->id}/book", [
            'starts_at' => $startsAt->toIso8601String(),
        ])->assertSessionHasErrors('booking');

        $this->assertSame(1, TeacherAppointment::count());
    }

    public function test_booking_outside_availability_is_rejected(): void
    {
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();

        $startsAt = CarbonImmutable::now('UTC')->addDays(3)->setTime(20, 0); // outside 09–17

        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/book", [
            'starts_at' => $startsAt->toIso8601String(),
        ])->assertSessionHasErrors('booking');
    }

    public function test_student_cancel_and_reschedule_flow(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();

        $service = app(TeacherSchedulingService::class);
        $appointment = $service->request($teacher, $student, CarbonImmutable::now('UTC')->addDays(3)->setTime(11, 0));
        $service->confirm($appointment, $teacher);

        // Student requests a new time.
        $newTime = CarbonImmutable::now('UTC')->addDays(4)->setTime(14, 0);
        $this->actingAs($student)->post("/my-appointments/{$appointment->id}/reschedule", [
            'starts_at' => $newTime->toDateTimeString(),
        ])->assertSessionHas('status', 'reschedule-requested');

        $appointment->refresh();
        $this->assertSame(TeacherAppointment::STATUS_RESCHEDULE_REQUESTED, $appointment->status);

        // Teacher approves → appointment moves to the requested time.
        $this->actingAs($teacher)->post("/teacher/appointments/{$appointment->id}/confirm");
        $appointment->refresh();
        $this->assertTrue($appointment->isConfirmed());
        $this->assertTrue($appointment->starts_at->equalTo($newTime->utc()));

        // Student cancels.
        $this->actingAs($student)->post("/my-appointments/{$appointment->id}/cancel");
        $this->assertSame(TeacherAppointment::STATUS_CANCELLED_BY_STUDENT, $appointment->fresh()->status);
    }

    public function test_invalid_state_transition_is_blocked(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();

        $service = app(TeacherSchedulingService::class);
        $appointment = $service->request($teacher, $student, CarbonImmutable::now('UTC')->addDays(3)->setTime(9, 0));

        // Completing a pending (unconfirmed) appointment is invalid.
        $this->actingAs($teacher)->post("/teacher/appointments/{$appointment->id}/complete")
            ->assertSessionHasErrors('appointment');

        $this->assertSame(TeacherAppointment::STATUS_PENDING, $appointment->fresh()->status);
    }

    public function test_stranger_cannot_manage_appointment(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $this->openBooking($teacher);
        $student = User::factory()->create();
        $service = app(TeacherSchedulingService::class);
        $appointment = $service->request($teacher, $student, CarbonImmutable::now('UTC')->addDays(3)->setTime(9, 0));

        $otherTeacher = $this->premiumTeacher();
        $this->actingAs($otherTeacher)->post("/teacher/appointments/{$appointment->id}/confirm")->assertForbidden();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->post("/my-appointments/{$appointment->id}/cancel")->assertForbidden();
    }

    public function test_basic_teacher_cannot_manage_calendar(): void
    {
        $teacher = $this->basicTeacher();

        $this->actingAs($teacher)->get('/teacher/calendar')->assertForbidden();
    }

    // ── Reviews ──────────────────────────────────────────────────────────────

    public function test_connected_student_can_review_and_stats_appear(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);

        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/reviews", [
            'rating' => 5,
            'body' => 'Wonderful teacher!',
        ])->assertSessionHas('status', 'review-saved');

        $this->assertDatabaseHas('teacher_reviews', [
            'student_id' => $student->id,
            'rating' => 5,
            'status' => 'approved',
        ]);
        Notification::assertSentTo($teacher, TeacherReviewReceived::class);

        // Public profile shows the rating.
        $this->get("/teachers/teacher-{$teacher->id}")->assertOk()->assertSee('Wonderful teacher!');
    }

    public function test_unrelated_user_cannot_review(): void
    {
        $teacher = $this->premiumTeacher();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->post("/teachers/teacher-{$teacher->id}/reviews", ['rating' => 1])
            ->assertForbidden();
    }

    public function test_review_is_upserted_not_duplicated(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);

        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/reviews", ['rating' => 4]);
        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/reviews", ['rating' => 5, 'body' => 'Updated']);

        $this->assertSame(1, TeacherReview::count());
        $this->assertSame(5, TeacherReview::first()->rating);
    }

    public function test_hidden_review_is_not_public_and_admin_can_moderate(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);
        $this->actingAs($student)->post("/teachers/teacher-{$teacher->id}/reviews", ['rating' => 1, 'body' => 'Nasty text']);
        $review = TeacherReview::first();

        // Teacher reports it.
        $this->actingAs($teacher)->post("/teacher-reviews/{$review->id}/report", ['reason' => 'Abusive'])
            ->assertSessionHas('status', 'review-reported');

        // Admin hides it.
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post("/admin/teacher-reviews/{$review->id}/hide");

        $this->assertSame('hidden', $review->fresh()->status);
        $this->get("/teachers/teacher-{$teacher->id}")->assertOk()->assertDontSee('Nasty text');
    }

    // ── Page smoke tests ─────────────────────────────────────────────────────

    public function test_package3_pages_render(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->connect($teacher, $student);
        $this->openBooking($teacher);

        $this->actingAs($teacher)->get('/teacher/messages')->assertOk();
        $this->actingAs($teacher)->get('/teacher/calendar')->assertOk();
        $this->actingAs($student)->get('/teacher-messages')->assertOk();
        $this->actingAs($student)->get('/my-appointments')->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/teacher-reviews')->assertOk();

        // Public profile renders with the booking panel for guests.
        $this->get("/teachers/teacher-{$teacher->id}")->assertOk();
    }
}
