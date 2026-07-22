<?php

namespace Tests\Feature;

use App\Mail\TeacherStudentInvitationMail;
use App\Models\ChordPractice;
use App\Models\LearningPathExercise;
use App\Models\ScalePractice;
use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentAttempt;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherProfile;
use App\Models\TeacherStudentInvitation;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Models\UserPractice;
use App\Notifications\Teacher\StudentAssignmentCompleted;
use App\Notifications\Teacher\StudentAssignmentReceived;
use App\Notifications\Teacher\TeacherRelationshipRequested;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmGroupingService;
use App\Services\Teacher\TeacherAssignmentConfigFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeacherCrmPackage2Test extends TestCase
{
    use RefreshDatabase;

    private function premiumTeacher(): User
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $teacher->id,
            'slug' => 'teacher-'.$teacher->id,
            'tier' => TeacherProfile::TIER_PREMIUM,
            'status' => TeacherProfile::STATUS_APPROVED,
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

    private function activeRelationship(User $teacher, User $student): TeacherStudentRelationship
    {
        return TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);
    }

    // ── Invitations & relationships ──────────────────────────────────────────

    public function test_premium_teacher_can_send_relationship_request_to_existing_user(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();

        $this->actingAs($teacher)
            ->post('/teacher/relationships', ['user_id' => $student->id])
            ->assertSessionHas('status', 'relationship-requested');

        $this->assertDatabaseHas('teacher_student_relationships', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
        ]);
        Notification::assertSentTo($student, TeacherRelationshipRequested::class);
    }

    public function test_basic_teacher_can_manage_students_within_quota(): void
    {
        Notification::fake();

        $teacher = $this->basicTeacher();
        $student = User::factory()->create(['plan' => 'free']);

        // Basic tier may now add students (capped by max_free_students).
        $this->actingAs($teacher)
            ->post('/teacher/relationships', ['user_id' => $student->id])
            ->assertSessionHas('status', 'relationship-requested');

        $this->actingAs($teacher)->get('/teacher/students')->assertOk();
    }

    public function test_student_can_approve_pending_relationship(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $rel = TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
        ]);

        $this->actingAs($student)->post("/my-teachers/{$rel->id}/approve");

        $this->assertSame(TeacherStudentRelationship::STATUS_ACTIVE, $rel->fresh()->status);
        $this->assertNotNull($rel->fresh()->approved_at);
    }

    public function test_another_user_cannot_approve_someone_elses_relationship(): void
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $other = User::factory()->create();
        $rel = TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
        ]);

        $this->actingAs($other)->post("/my-teachers/{$rel->id}/approve")->assertForbidden();
    }

    public function test_email_invitation_is_created_and_mail_queued(): void
    {
        Mail::fake();
        $teacher = $this->premiumTeacher();

        $this->actingAs($teacher)
            ->post('/teacher/invitations', ['email' => 'new-student@example.com', 'name' => 'New Kid'])
            ->assertSessionHas('status', 'invitation-sent');

        $this->assertDatabaseHas('teacher_student_invitations', [
            'teacher_id' => $teacher->id,
            'email' => 'new-student@example.com',
            'status' => 'pending',
        ]);
        Mail::assertQueued(TeacherStudentInvitationMail::class);
    }

    public function test_duplicate_email_invitation_is_rejected(): void
    {
        Mail::fake();
        $teacher = $this->premiumTeacher();

        $this->actingAs($teacher)->post('/teacher/invitations', ['email' => 'kid@example.com']);
        $this->actingAs($teacher)
            ->post('/teacher/invitations', ['email' => 'kid@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, TeacherStudentInvitation::count());
    }

    public function test_accepting_invitation_creates_active_relationship_and_joins_class(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $class = TeacherClass::create(['teacher_id' => $teacher->id, 'name' => 'Piano A']);
        $invitation = TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => 'email',
            'email' => 'kid@example.com',
            'token' => TeacherStudentInvitation::generateToken(),
            'teacher_class_id' => $class->id,
            'expires_at' => now()->addDays(7),
        ]);
        $student = User::factory()->create(['email' => 'kid@example.com']);

        $this->actingAs($student)
            ->post("/teacher-invitations/{$invitation->token}")
            ->assertRedirect(route('my-teachers.index'));

        $this->assertDatabaseHas('teacher_student_relationships', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('teacher_class_students', [
            'teacher_class_id' => $class->id,
            'student_id' => $student->id,
        ]);
        $this->assertSame('accepted', $invitation->fresh()->status);
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $teacher = $this->premiumTeacher();
        $invitation = TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => 'email',
            'email' => 'kid@example.com',
            'token' => TeacherStudentInvitation::generateToken(),
            'expires_at' => now()->subDay(),
        ]);
        $student = User::factory()->create();

        $this->actingAs($student)->post("/teacher-invitations/{$invitation->token}");

        $this->assertDatabaseMissing('teacher_student_relationships', [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_revoked_link_invitation_cannot_be_used(): void
    {
        $teacher = $this->premiumTeacher();
        $invitation = TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => 'link',
            'token' => TeacherStudentInvitation::generateToken(),
            'status' => 'revoked',
        ]);
        $student = User::factory()->create();

        $this->actingAs($student)->post("/teacher-invitations/{$invitation->token}");

        $this->assertSame(0, TeacherStudentRelationship::count());
    }

    public function test_link_invitation_is_multi_use(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $invitation = TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => 'link',
            'token' => TeacherStudentInvitation::generateToken(),
        ]);

        foreach (User::factory()->count(2)->create() as $student) {
            $this->actingAs($student)->post("/teacher-invitations/{$invitation->token}");
        }

        $this->assertSame(2, TeacherStudentRelationship::active()->count());
        $this->assertSame('pending', $invitation->fresh()->status);
    }

    public function test_student_revocation_removes_class_membership_and_access(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $rel = $this->activeRelationship($teacher, $student);
        $class = TeacherClass::create(['teacher_id' => $teacher->id, 'name' => 'Piano A']);
        $class->students()->attach($student->id);

        $this->actingAs($student)->delete("/my-teachers/{$rel->id}");

        $this->assertSame(TeacherStudentRelationship::STATUS_REVOKED_BY_STUDENT, $rel->fresh()->status);
        $this->assertDatabaseMissing('teacher_class_students', [
            'teacher_class_id' => $class->id,
            'student_id' => $student->id,
        ]);
        // Teacher can no longer open the student profile.
        $this->actingAs($teacher)->get("/teacher/students/{$student->id}")->assertForbidden();
    }

    // ── Student CRM authorization ────────────────────────────────────────────

    public function test_teacher_can_view_active_student_profile(): void
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->activeRelationship($teacher, $student);

        $this->actingAs($teacher)->get("/teacher/students/{$student->id}")->assertOk();
    }

    public function test_teacher_cannot_view_unrelated_student_profile(): void
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();

        $this->actingAs($teacher)->get("/teacher/students/{$student->id}")->assertForbidden();
    }

    public function test_notes_tags_rewards_require_active_relationship(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();

        $this->actingAs($teacher)
            ->post("/teacher/students/{$student->id}/notes", ['body' => 'x'])
            ->assertForbidden();

        $this->activeRelationship($teacher, $student);

        $this->actingAs($teacher)
            ->post("/teacher/students/{$student->id}/notes", ['body' => 'Great progress'])
            ->assertSessionHas('status', 'note-added');
        $this->actingAs($teacher)
            ->post("/teacher/students/{$student->id}/rewards", ['type' => 'sticker', 'label' => 'Rhythm Star'])
            ->assertSessionHas('status', 'reward-given');

        $this->assertDatabaseHas('teacher_student_notes', ['teacher_id' => $teacher->id, 'student_id' => $student->id]);
        $this->assertDatabaseHas('teacher_student_rewards', ['label' => 'Rhythm Star']);
    }

    // ── Classes ──────────────────────────────────────────────────────────────

    public function test_class_crud_and_membership_requires_active_relationship(): void
    {
        $teacher = $this->premiumTeacher();
        $stranger = User::factory()->create();

        $this->actingAs($teacher)->post('/teacher/classes', ['name' => 'Piano A'])
            ->assertSessionHas('status', 'class-created');
        $class = TeacherClass::first();

        // Adding a student without an active relationship is forbidden.
        $this->actingAs($teacher)
            ->post("/teacher/classes/{$class->id}/students", ['student_id' => $stranger->id])
            ->assertForbidden();

        $student = User::factory()->create();
        $this->activeRelationship($teacher, $student);
        $this->actingAs($teacher)
            ->post("/teacher/classes/{$class->id}/students", ['student_id' => $student->id])
            ->assertSessionHas('status', 'student-added');
    }

    public function test_teacher_cannot_touch_another_teachers_class(): void
    {
        $teacherA = $this->premiumTeacher();
        $teacherB = $this->premiumTeacher();
        $class = TeacherClass::create(['teacher_id' => $teacherA->id, 'name' => 'A']);

        $this->actingAs($teacherB)->get("/teacher/classes/{$class->id}")->assertForbidden();
        $this->actingAs($teacherB)->put("/teacher/classes/{$class->id}", ['name' => 'Hack'])->assertForbidden();
    }

    // ── Assignments: generation, snapshots, sending ──────────────────────────

    public function test_teacher_creates_assignment_draft_with_canonical_questions(): void
    {
        $teacher = $this->premiumTeacher();

        $response = $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'Interval homework',
            'type' => 'exercise',
            'practice_type' => 'melodic-interval-practice',
            'difficulty' => 'beginner',
            'question_count' => 5,
        ]);

        $assignment = TeacherAssignment::first();
        $response->assertRedirect(route('teacher.assignments.show', $assignment));
        $this->assertSame('draft', $assignment->status);
        $this->assertSame(5, $assignment->questions()->count());

        // Every snapshot must yield a canonical correct answer.
        $generator = app(LearningPathQuestionGenerator::class);
        foreach ($assignment->questions as $q) {
            $answer = $generator->getAnswerFromSessionQuestion($q->question_data, 'melodic-interval-practice');
            $this->assertNotSame('', $answer);
        }
    }

    public function test_unsupported_config_is_rejected_with_clear_error(): void
    {
        $teacher = $this->premiumTeacher();

        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'Bad homework',
            'type' => 'exercise',
            'practice_type' => 'melodic-interval-practice',
            'difficulty' => 'beginner',
            'question_count' => 5,
            'overrides' => json_encode(['allowed_intervals' => ['Quintuple 19th']]),
        ])->assertSessionHasErrors('practice_type');

        $this->assertSame(0, TeacherAssignment::count());
    }

    public function test_sending_assignment_only_reaches_active_students(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $active = User::factory()->create();
        $pending = User::factory()->create();
        $this->activeRelationship($teacher, $active);
        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $pending->id,
            'status' => TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
        ]);

        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::first();

        $this->actingAs($teacher)->post("/teacher/assignments/{$assignment->id}/send", [
            'student_ids' => [$active->id, $pending->id],
        ])->assertSessionHas('status', 'assignment-sent');

        $this->assertSame('sent', $assignment->fresh()->status);
        $this->assertSame(1, $assignment->recipients()->count());
        $this->assertSame($active->id, $assignment->recipients()->first()->student_id);
        Notification::assertSentTo($active, StudentAssignmentReceived::class);
    }

    public function test_sent_assignment_questions_are_locked(): void
    {
        Notification::fake();
        [$teacher, $assignment] = $this->sentAssignment();

        $firstQuestion = $assignment->questions()->first();
        $originalData = $firstQuestion->question_data;

        $this->actingAs($teacher)
            ->post("/teacher/assignments/{$assignment->id}/regenerate")
            ->assertSessionHasErrors('questions');
        $this->actingAs($teacher)
            ->post("/teacher/assignments/{$assignment->id}/questions/{$firstQuestion->id}/regenerate")
            ->assertSessionHasErrors('questions');
        $this->actingAs($teacher)
            ->delete("/teacher/assignments/{$assignment->id}/questions/{$firstQuestion->id}")
            ->assertSessionHasErrors('questions');

        $this->assertSame($originalData, $firstQuestion->fresh()->question_data);
    }

    // ── Assignments: editing questions & teacher preview ─────────────────────

    public function test_teacher_can_edit_question_content_in_draft(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['target' => 'G', 'octave' => '5'],
                'options' => 'G, A, B, C',
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        $this->assertSame('G', $data['target']);
        $this->assertSame('5', $data['octave']);
        // single-note keeps other_options as a comma-separated string including the answer.
        $this->assertSame('G,A,B,C', $data['other_options']);

        // The edited snapshot must still yield the (new) canonical answer.
        $answer = app(LearningPathQuestionGenerator::class)
            ->getAnswerFromSessionQuestion($data, 'single-note-practice');
        $this->assertSame('G', $answer);
    }

    public function test_editing_distractors_from_abcd_editor_keeps_correct_answer(): void
    {
        // The A/B/C/D editor submits distractors only, one per line, with the
        // (audio-tied) correct answer excluded. Interval types store the choice
        // list in `options` including the correct answer, so it must be re-merged
        // and reshuffled — never dropped or duplicated.
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'melodic-interval-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        $correct = app(LearningPathQuestionGenerator::class)
            ->getAnswerFromSessionQuestion($question->question_data, 'melodic-interval-practice');
        // Distractors must not accidentally equal the correct answer.
        $distractors = array_values(array_diff(['Perfect 5th', 'Major 6th', 'Minor 7th'], [$correct]));

        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => [],
                'options' => implode("\n", $distractors),
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        // Interval choices live in `options`, contain the correct answer exactly
        // once, include every submitted distractor, and are always topped back
        // up to a full 4-choice set (an edit can never shrink the question).
        $this->assertIsArray($data['options']);
        $this->assertContains($correct, $data['options']);
        $this->assertSame(1, count(array_keys($data['options'], $correct, true)));
        foreach ($distractors as $d) {
            $this->assertContains($d, $data['options']);
        }
        $this->assertCount(4, $data['options']);
        $this->assertSame(4, count(array_unique($data['options'])));
    }

    public function test_editing_interval_recomputes_second_note_from_interval(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'melodic-interval-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['note1' => 'C', 'octave' => '4', 'interval' => 'Perfect 5th', 'direction' => 'ascending', 'clef' => 'treble'],
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        // note2 is derived, never stale: a Perfect 5th above C4 is G4.
        $this->assertSame('G', $data['note2']);
        $this->assertSame('Perfect 5th', $data['interval']);
        // The choice list contains the new correct answer + 3 distractors.
        $this->assertContains('Perfect 5th', $data['options']);
        $this->assertCount(4, $data['options']);
    }

    public function test_editing_single_note_clef_stores_clef_and_reference(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['target' => 'C', 'octave' => '3', 'clef' => 'bass', 'reference_note' => ''],
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        $this->assertSame('bass', $data['clef']);
        // An empty reference is auto-filled with a natural in the same octave.
        $this->assertMatchesRegularExpression('/^[A-G]3$/', $data['reference_note']);
        // The keyboard/note-name option list still contains the answer.
        $this->assertContains('C', explode(',', $data['other_options']));

        // A note outside the bass range is rejected loudly, not stored.
        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['target' => 'C', 'octave' => '6', 'clef' => 'bass'],
            ])->assertSessionHasErrors('questions');
        $this->assertSame('3', $question->fresh()->question_data['octave']);
    }

    public function test_editing_rhythm_time_signature_regenerates_matching_options(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'rhythm-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        // Switch the question to 3/4 with a matching hand-built pattern.
        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => [
                    'time_signature' => '3/4', 'bars' => '1', 'tempo' => '90',
                    'note_values' => 'quarter, eighth, eighth, quarter',
                ],
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        $this->assertSame('3/4', $data['time_signature']);
        $this->assertSame(['quarter', 'eighth', 'eighth', 'quarter'], $data['note_values']);

        // Every regenerated distractor fills exactly one bar of 3/4 — options
        // can never keep durations from the previous meter.
        $grouping = app(RhythmGroupingService::class);
        $this->assertNotEmpty($data['other_options']);
        foreach ($data['other_options'] as $option) {
            $total = array_sum(array_map(fn ($t) => $grouping->noteTwelfths($t), $option));
            $this->assertSame(36, $total);
        }

        // A pattern that does not fill the meter is rejected.
        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['time_signature' => '3/4', 'bars' => '1', 'tempo' => '90', 'note_values' => 'quarter, quarter'],
            ])->assertSessionHasErrors('questions');
    }

    public function test_editing_dictation_time_signature_regenerates_melody_in_sync(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'melodic-dictation', 'difficulty' => 'intermediate', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->orderBy('position')->first();

        $original = $question->question_data;
        $this->assertSame('4/4', $original['time_signature']);
        $this->assertCount(count($original['notes']), $original['note_values']);

        // Changing the meter regenerates rhythm + melody to fit it.
        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['time_signature' => '3/4'],
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        $this->assertSame('3/4', $data['time_signature']);
        $this->assertCount(count($data['notes']), $data['note_values']);

        $grouping = app(RhythmGroupingService::class);
        $total = array_sum(array_map(fn ($t) => $grouping->noteTwelfths($t), $data['note_values']));
        $this->assertSame(($data['bars'] ?? 1) * 36, $total);

        // Explicit regeneration with chosen rhythm values is honoured.
        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => [],
                'rhythm_values' => ['quarter'],
                'regenerate_melody' => '1',
            ])->assertSessionHas('status', 'question-updated');

        $data = $question->fresh()->question_data;
        $this->assertSame(['quarter'], array_values(array_unique($data['note_values'])));
        $this->assertCount(count($data['notes']), $data['note_values']);
    }

    public function test_editing_a_question_is_blocked_after_send(): void
    {
        Notification::fake();
        [$teacher, $assignment] = $this->sentAssignment();
        $question = $assignment->questions()->first();
        $original = $question->question_data;

        $this->actingAs($teacher)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['target' => 'G'],
            ])->assertSessionHasErrors('questions');

        $this->assertSame($original, $question->fresh()->question_data);
    }

    public function test_teacher_cannot_edit_another_teachers_question(): void
    {
        $teacherA = $this->premiumTeacher();
        $teacherB = $this->premiumTeacher();
        $this->actingAs($teacherA)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $question = $assignment->questions()->first();

        $this->actingAs($teacherB)
            ->put("/teacher/assignments/{$assignment->id}/questions/{$question->id}", [
                'fields' => ['target' => 'G'],
            ])->assertForbidden();
    }

    public function test_teacher_preview_grades_answers_without_recording_anything(): void
    {
        $teacher = $this->premiumTeacher();
        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();

        $this->actingAs($teacher)
            ->get("/teacher/assignments/{$assignment->id}/preview")
            ->assertRedirect('/practice/single-note-practice');

        $session = session('teacher_assignment_preview_session');
        $this->assertNotNull($session);
        $this->assertSame($assignment->id, $session['assignment_id']);

        // The practice page serves the snapshot questions to the teacher.
        $this->actingAs($teacher)->get('/practice/single-note-practice')->assertOk();

        $generator = app(LearningPathQuestionGenerator::class);
        $last = null;
        foreach ($session['questions'] as $i => $data) {
            $correct = $generator->getAnswerFromSessionQuestion($data, 'single-note-practice');
            $last = $this->actingAs($teacher)->postJson('/api/practice/check-answer', [
                'question_id' => $i + 1,
                'answer' => $correct,
                'slug' => 'single-note-practice',
            ]);
            $last->assertOk()->assertJson(['is_correct' => true, 'preview' => true]);
        }

        $last->assertJson(['completed' => true, 'score' => 100]);

        // Preview grades but persists nothing: no attempt, recipient, or teacher stat.
        $this->assertSame(0, TeacherAssignmentRecipient::count());
        $this->assertSame(0, TeacherAssignmentAttempt::count());
        $this->assertSame(0, UserPractice::where('user_id', $teacher->id)->count());
        $this->assertNull(session('teacher_assignment_preview_session'));
    }

    public function test_teacher_cannot_preview_another_teachers_assignment(): void
    {
        $teacherA = $this->premiumTeacher();
        $teacherB = $this->premiumTeacher();
        $this->actingAs($teacherA)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();

        $this->actingAs($teacherB)
            ->get("/teacher/assignments/{$assignment->id}/preview")
            ->assertForbidden();
    }

    // ── Student play flow: canonical snapshot end-to-end ─────────────────────

    public function test_student_completes_assignment_with_snapshot_evaluation(): void
    {
        Notification::fake();
        [$teacher, $assignment, $student] = $this->sentAssignment(returnStudent: true);
        $recipient = $assignment->recipients()->first();

        // Start the attempt.
        $this->actingAs($student)
            ->post("/assignments/{$recipient->id}/start")
            ->assertRedirect('/practice/single-note-practice');

        $session = session('teacher_assignment_session');
        $this->assertNotNull($session);
        $this->assertSame($assignment->id, $session['assignment_id']);

        // The practice page serves the snapshot questions.
        $this->actingAs($student)->get('/practice/single-note-practice')->assertOk();

        // Answer every question with the canonical correct answer from the snapshot.
        $generator = app(LearningPathQuestionGenerator::class);
        foreach ($session['questions'] as $i => $questionData) {
            $correct = $generator->getAnswerFromSessionQuestion($questionData, 'single-note-practice');
            $response = $this->actingAs($student)->postJson('/api/practice/check-answer', [
                'question_id' => $i + 1,
                'answer' => $correct,
                'slug' => 'single-note-practice',
            ]);
            $response->assertOk()->assertJson(['is_correct' => true]);
        }

        $recipient->refresh();
        $this->assertSame(TeacherAssignmentRecipient::STATUS_COMPLETED, $recipient->status);
        $this->assertEquals(100.0, (float) $recipient->best_score);
        $this->assertNull(session('teacher_assignment_session'));

        // Teacher notified; assignment completed since the only recipient finished.
        Notification::assertSentTo($teacher, StudentAssignmentCompleted::class);
        $this->assertSame('completed', $assignment->fresh()->status);
    }

    public function test_completion_grants_attached_reward(): void
    {
        Notification::fake();
        [$teacher, $assignment, $student] = $this->sentAssignment(returnStudent: true, reward: 'Note Master');
        $recipient = $assignment->recipients()->first();

        $this->actingAs($student)->post("/assignments/{$recipient->id}/start");
        $session = session('teacher_assignment_session');
        $generator = app(LearningPathQuestionGenerator::class);
        foreach ($session['questions'] as $i => $questionData) {
            $this->actingAs($student)->postJson('/api/practice/check-answer', [
                'question_id' => $i + 1,
                'answer' => $generator->getAnswerFromSessionQuestion($questionData, 'single-note-practice'),
                'slug' => 'single-note-practice',
            ]);
        }

        $this->assertDatabaseHas('teacher_student_rewards', [
            'student_id' => $student->id,
            'teacher_assignment_id' => $assignment->id,
            'label' => 'Note Master',
        ]);
    }

    public function test_max_attempts_is_enforced(): void
    {
        Notification::fake();
        [, $assignment, $student] = $this->sentAssignment(returnStudent: true, maxAttempts: 1);
        $recipient = $assignment->recipients()->first();

        $this->actingAs($student)->post("/assignments/{$recipient->id}/start")
            ->assertRedirect('/practice/single-note-practice');
        // Attempt used; a second start must fail.
        $this->actingAs($student)->post("/assignments/{$recipient->id}/start")
            ->assertSessionHasErrors('assignment');
    }

    public function test_student_cannot_start_someone_elses_assignment(): void
    {
        Notification::fake();
        [, $assignment] = $this->sentAssignment();
        $recipient = $assignment->recipients()->first();
        $other = User::factory()->create();

        $this->actingAs($other)->post("/assignments/{$recipient->id}/start")->assertForbidden();
    }

    public function test_interval_direction_non_regression_in_assignment_flow(): void
    {
        Notification::fake();
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->activeRelationship($teacher, $student);

        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'Direction HW', 'type' => 'exercise',
            'practice_type' => 'interval-direction-practice', 'difficulty' => 'beginner', 'question_count' => 5,
        ]);
        $assignment = TeacherAssignment::first();
        $this->actingAs($teacher)->post("/teacher/assignments/{$assignment->id}/send", ['student_ids' => [$student->id]]);

        $music = app(MusicTheoryService::class);
        foreach ($assignment->questions as $q) {
            $d = $q->question_data;
            // Canonical rule: direction derives from actual pitches, firstNote is start.
            $expected = $music->getDirection(
                $d['note1'], (int) ($d['octave'] ?? 4),
                $d['note2'], (int) ($d['note2_octave'] ?? ($d['octave'] ?? 4)),
            );
            $answer = app(LearningPathQuestionGenerator::class)
                ->getAnswerFromSessionQuestion($d, 'interval-direction-practice');
            $this->assertSame($expected, $answer);
        }
    }

    /**
     * Every pitch in every generated assignment question must sit inside the
     * clef's playable range (site standard: treble G3–G5, bass C2–C4,
     * alto C3–C5) — the same rule the Exercise Setup Studio flow enforces.
     */
    public function test_assignment_questions_stay_within_clef_ranges(): void
    {
        $factory = app(TeacherAssignmentConfigFactory::class);
        $generator = app(LearningPathQuestionGenerator::class);
        $music = app(MusicTheoryService::class);

        $pitchedTypes = array_values(array_diff(TeacherAssignmentConfigFactory::PRACTICE_TYPES, ['rhythm-practice']));
        $clefOverridable = ['interval-direction-practice', 'interval-comparison-practice', 'melodic-dictation', 'chord-practice', 'scale-practice'];

        foreach ($pitchedTypes as $type) {
            $clefs = in_array($type, $clefOverridable, true) ? ['treble', 'bass'] : ['treble'];

            foreach (TeacherAssignmentConfigFactory::DIFFICULTIES as $difficulty) {
                foreach ($clefs as $clef) {
                    $config = $factory->build($type, $difficulty, $clef === 'treble' ? [] : ['clef' => $clef]);
                    $exercise = new LearningPathExercise;
                    $exercise->config_json = $config;

                    $questions = $generator->generate($exercise, 6);
                    $this->assertGreaterThan(0, $questions->count(), "{$type}/{$difficulty}/{$clef} generated no questions");

                    [$min, $max] = $music->clefRangeMidi($clef);

                    foreach ($generator->serializeForSession($questions) as $data) {
                        foreach ($this->questionPitches($data, $type, $generator) as [$note, $octave]) {
                            $midi = $music->midiNumber($note, (int) $octave);
                            if ($midi === null) {
                                continue; // exotic spelling not in the note table
                            }
                            $this->assertGreaterThanOrEqual($min, $midi, "{$type}/{$difficulty}/{$clef}: {$note}{$octave} below clef range");
                            $this->assertLessThanOrEqual($max, $midi, "{$type}/{$difficulty}/{$clef}: {$note}{$octave} above clef range");
                        }
                    }
                }
            }
        }
    }

    /** All [note, octave] pairs a serialized question renders/plays. */
    private function questionPitches(array $data, string $type, LearningPathQuestionGenerator $generator): array
    {
        $parse = function (string $entry): ?array {
            return preg_match('/^([A-G][#b]?)(\d)$/', $entry, $m) ? [$m[1], (int) $m[2]] : null;
        };

        switch ($type) {
            case 'single-note-practice':
                return [[$data['target'], $data['octave'] ?? 4]];

            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
            case 'interval-direction-practice':
            case 'interval-construction-practice':
                $octave = $data['octave'] ?? 4;

                return [
                    [$data['note1'], $octave],
                    [$data['note2'], $data['note2_octave'] ?? $octave],
                ];

            case 'interval-comparison-practice':
                $octave = $data['octave'] ?? 4;
                $pitches = [];
                foreach (['interval_a', 'interval_b'] as $key) {
                    foreach (explode(',', (string) $data[$key]) as $note) {
                        $pitches[] = [trim($note), $octave];
                    }
                }

                return $pitches;

            case 'chord-practice':
            case 'scale-practice':
                $model = $generator->reconstructFromSession([$data], $type)->first();

                return array_values(array_filter(array_map($parse, $model->note_array ?? [])));

            case 'melodic-dictation':
                return array_values(array_filter(array_map($parse, $data['notes'] ?? [])));
        }

        return [];
    }

    /**
     * The factory's chord/scale vocabulary must be the canonical model names
     * (chordIntervals / scaleIntervals keys). A value outside these maps
     * silently falls back to Major intervals — audio would contradict the
     * stored correct answer.
     */
    public function test_factory_chord_and_scale_vocabulary_resolves_to_intervals(): void
    {
        foreach (TeacherAssignmentConfigFactory::CHORD_TYPES as $type) {
            $this->assertArrayHasKey($type, ChordPractice::chordIntervals(), "Chord type {$type} has no interval definition");
        }

        foreach (TeacherAssignmentConfigFactory::SCALE_TYPES as $type) {
            $this->assertArrayHasKey($type, ScalePractice::scaleIntervals(), "Scale type {$type} has no interval definition");
        }

        // Presets must also stick to the whitelists (types + distractors).
        $factory = app(TeacherAssignmentConfigFactory::class);
        foreach (TeacherAssignmentConfigFactory::DIFFICULTIES as $difficulty) {
            $chordCfg = $factory->build('chord-practice', $difficulty);
            foreach (array_merge($chordCfg['allowed_chord_types'], $chordCfg['distractor_pool']) as $type) {
                $this->assertContains($type, TeacherAssignmentConfigFactory::CHORD_TYPES);
            }
            $scaleCfg = $factory->build('scale-practice', $difficulty);
            foreach (array_merge($scaleCfg['allowed_scale_types'], $scaleCfg['distractor_pool']) as $type) {
                $this->assertContains($type, TeacherAssignmentConfigFactory::SCALE_TYPES);
            }
        }
    }

    public function test_config_factory_produces_generatable_configs_for_all_types(): void
    {
        $factory = app(TeacherAssignmentConfigFactory::class);
        $generator = app(LearningPathQuestionGenerator::class);

        foreach (TeacherAssignmentConfigFactory::PRACTICE_TYPES as $type) {
            foreach (TeacherAssignmentConfigFactory::DIFFICULTIES as $difficulty) {
                $config = $factory->build($type, $difficulty);
                $exercise = new LearningPathExercise;
                $exercise->config_json = $config;
                $questions = $generator->generate($exercise, 3);
                $this->assertGreaterThan(0, $questions->count(), "{$type}/{$difficulty} generated no questions");

                $serialized = $generator->serializeForSession($questions);
                foreach ($serialized as $data) {
                    $answer = $generator->getAnswerFromSessionQuestion($data, $type);
                    $this->assertNotSame('', $answer, "{$type}/{$difficulty} produced an empty canonical answer");
                }
            }
        }
    }

    // ── Page rendering smoke tests ───────────────────────────────────────────

    public function test_review_page_renders_previews_for_every_practice_type(): void
    {
        $teacher = $this->premiumTeacher();

        foreach (TeacherAssignmentConfigFactory::PRACTICE_TYPES as $type) {
            $this->actingAs($teacher)->post('/teacher/assignments', [
                'title' => "HW {$type}", 'type' => 'exercise',
                'practice_type' => $type, 'difficulty' => 'intermediate', 'question_count' => 3,
            ]);
            $assignment = TeacherAssignment::latest('id')->first();
            $this->actingAs($teacher)
                ->get("/teacher/assignments/{$assignment->id}")
                ->assertOk()
                ->assertSee($assignment->title);
        }
    }

    public function test_student_facing_pages_render(): void
    {
        Notification::fake();
        [, $assignment, $student] = $this->sentAssignment(returnStudent: true);

        $this->actingAs($student)->get('/assignments')->assertOk()->assertSee($assignment->title);
        $this->actingAs($student)->get('/my-teachers')->assertOk();
        $this->actingAs($student)->get('/teacher/dashboard')->assertForbidden();
    }

    public function test_teacher_pages_render(): void
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->activeRelationship($teacher, $student);
        TeacherClass::create(['teacher_id' => $teacher->id, 'name' => 'Piano A']);

        $this->actingAs($teacher)->get('/teacher/students')->assertOk();
        $this->actingAs($teacher)->get('/teacher/classes')->assertOk();
        $this->actingAs($teacher)->get('/teacher/assignments')->assertOk();
        $this->actingAs($teacher)->get('/teacher/assignments/create')->assertOk();
        $this->actingAs($teacher)->get('/teacher/dashboard')->assertOk();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function sentAssignment(bool $returnStudent = false, ?string $reward = null, ?int $maxAttempts = null): array
    {
        $teacher = $this->premiumTeacher();
        $student = User::factory()->create();
        $this->activeRelationship($teacher, $student);

        $this->actingAs($teacher)->post('/teacher/assignments', [
            'title' => 'HW', 'type' => 'exercise',
            'practice_type' => 'single-note-practice', 'difficulty' => 'beginner', 'question_count' => 3,
            'reward_label' => $reward,
            'max_attempts' => $maxAttempts,
        ]);
        $assignment = TeacherAssignment::latest('id')->first();
        $this->actingAs($teacher)->post("/teacher/assignments/{$assignment->id}/send", ['student_ids' => [$student->id]]);
        $assignment->refresh();

        return $returnStudent ? [$teacher, $assignment, $student] : [$teacher, $assignment];
    }
}
