<?php

namespace Tests\Feature;

use App\Models\TeacherPaymentLink;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherProfileEditorTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(string $tier = 'basic'): User
    {
        $user = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $user->id,
            'tier' => $tier,
            'status' => 'draft',
            'slug' => 'teacher-'.$user->id,
        ]);

        return $user->fresh();
    }

    public function test_teacher_can_save_profile_draft_with_repeatable_rows(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->put(route('teacher.profile.update'), [
            'headline' => 'Piano teacher',
            'expertise' => 'Piano Teacher',
            'about' => 'About me text',
            'teaching_formats' => ['online', 'hybrid'],
            'lesson_types' => 'Piano, Music Theory',
            'languages' => 'English, Turkish',
            'primary_instrument' => 'Piano',
            'instruments' => 'Violin, Guitar',
            'educations' => [
                ['institution' => 'State Conservatory', 'program' => 'BA', 'field_of_study' => 'Piano', 'graduation_year' => 2015],
                ['institution' => 'Music Academy', 'program' => 'MA', 'field_of_study' => 'Pedagogy', 'graduation_year' => 2018],
            ],
            'show_email' => 1,
            'public_email' => 'teach@example.com',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();

        $profile = $teacher->teacherProfile->fresh();
        $this->assertSame('Piano teacher', $profile->headline);
        $this->assertSame(['Piano', 'Music Theory'], $profile->lesson_types);
        $this->assertSame(['online', 'hybrid'], $profile->teaching_formats);
        $this->assertSame(TeacherProfile::STATUS_DRAFT, $profile->status);
        $this->assertCount(2, $profile->educations);
        $this->assertCount(2, $profile->instruments);
        $this->assertTrue($profile->show_email);
    }

    public function test_teacher_can_add_a_service_and_valid_youtube_video(): void
    {
        $teacher = $this->makeTeacher();

        $this->actingAs($teacher)->post(route('teacher.services.store'), [
            'title' => '30-Minute Piano Lesson',
            'lesson_type' => 'Piano',
            'duration_minutes' => 30,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('teacher_services', ['title' => '30-Minute Piano Lesson']);

        $this->actingAs($teacher)->post(route('teacher.videos.store'), [
            'title' => 'My performance',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('teacher_videos', ['youtube_id' => 'dQw4w9WgXcQ']);
    }

    public function test_invalid_youtube_url_is_rejected(): void
    {
        $teacher = $this->makeTeacher();

        $this->actingAs($teacher)->post(route('teacher.videos.store'), [
            'title' => 'Bad video',
            'url' => 'https://example.com/watch?v=notyoutube',
        ])->assertSessionHasErrors('url');

        $this->assertDatabaseCount('teacher_videos', 0);
    }

    public function test_teacher_cannot_delete_another_teachers_service(): void
    {
        $a = $this->makeTeacher();
        $b = $this->makeTeacher();

        $service = $a->teacherProfile->services()->create(['title' => 'Lesson A']);

        $this->actingAs($b)->delete(route('teacher.services.destroy', $service))->assertForbidden();
        $this->assertDatabaseHas('teacher_services', ['id' => $service->id]);
    }

    // --- Payment links (Teacher Premium only) ---

    public function test_basic_teacher_cannot_add_payment_links(): void
    {
        $teacher = $this->makeTeacher('basic');

        $this->actingAs($teacher)->post(route('teacher.payment-links.store'), [
            'label' => 'Trial Lesson',
            'url' => 'https://pay.example.com/trial',
            'visibility' => 'public',
        ])->assertForbidden();
    }

    public function test_premium_teacher_can_add_payment_links(): void
    {
        $teacher = $this->makeTeacher('premium');

        $this->actingAs($teacher)->post(route('teacher.payment-links.store'), [
            'label' => 'Trial Lesson',
            'url' => 'https://pay.example.com/trial',
            'visibility' => TeacherPaymentLink::VISIBILITY_PUBLIC,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('teacher_payment_links', ['label' => 'Trial Lesson']);
    }

    public function test_payment_links_require_https(): void
    {
        $teacher = $this->makeTeacher('premium');

        $this->actingAs($teacher)->post(route('teacher.payment-links.store'), [
            'label' => 'Trial Lesson',
            'url' => 'http://insecure.example.com/pay',
            'visibility' => 'public',
        ])->assertSessionHasErrors('url');
    }

    public function test_only_public_payment_links_appear_on_public_profile(): void
    {
        $teacher = $this->makeTeacher('premium');
        $profile = $teacher->teacherProfile;
        $profile->update(['status' => TeacherProfile::STATUS_APPROVED]);

        $profile->paymentLinks()->create([
            'label' => 'Public Lesson Link', 'url' => 'https://pay.example.com/a', 'visibility' => 'public',
        ]);
        $profile->paymentLinks()->create([
            'label' => 'Students Only Link', 'url' => 'https://pay.example.com/b', 'visibility' => 'approved_students',
        ]);

        $response = $this->get('/teachers/'.$profile->slug);

        $response->assertSee('Public Lesson Link');
        $response->assertDontSee('Students Only Link');
    }

    // --- Media privacy ---

    public function test_private_documents_are_not_downloadable_by_other_users(): void
    {
        Storage::fake('local');

        $teacher = $this->makeTeacher();
        $other = User::factory()->create();

        $this->actingAs($teacher)->post(route('teacher.media.store'), [
            'kind' => 'document',
            'visibility' => 'private',
            'file' => UploadedFile::fake()->create('diploma.pdf', 200, 'application/pdf'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $media = $teacher->teacherProfile->media()->first();
        $this->assertNotNull($media);
        $this->assertSame('private', $media->visibility);

        $this->actingAs($other)->get(route('teacher.media.download', $media))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher.media.download', $media))->assertOk();
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        Storage::fake('local');
        $teacher = $this->makeTeacher();

        $this->actingAs($teacher)->post(route('teacher.media.store'), [
            'kind' => 'document',
            'visibility' => 'private',
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }
}
