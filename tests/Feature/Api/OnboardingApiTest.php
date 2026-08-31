<?php

namespace Tests\Feature\Api;

use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Database\Seeders\QuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app's onboarding answers, arriving at the profile and the survey.
 *
 * The interesting assertions are the ones about what is *not* written: a topic
 * the app did not ask about, and a field the app knows nothing about, both have
 * to survive this untouched. Everything else the learner would have to type
 * again on the website.
 */
class OnboardingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(QuestionnaireSeeder::class);
    }

    /** The answers a learner who plays piano and sings, and reads music, gives. */
    private function answers(array $overrides = []): array
    {
        return array_merge([
            'goals' => ['theory', 'exam'],
            'instruments' => ['piano', 'voice'],
            'topics' => ['intervals' => 'know', 'notation' => 'fluent'],
            'minutes_per_day' => 10,
            'completed_at' => '2026-08-31',
        ], $overrides);
    }

    private function answerTo(User $user, string $key): ?string
    {
        $question = QuestionnaireQuestion::where('key', $key)->firstOrFail();

        return QuestionnaireResponse::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->value('answer_value');
    }

    public function test_the_answers_fill_the_learner_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/onboarding', $this->answers())
            ->assertOk()
            ->assertJsonPath('data.profile_updated', true);

        $profile = $user->fresh()->profile;

        $this->assertSame('Piano', $profile->primary_instrument);
        $this->assertSame(['Voice'], $profile->secondary_instruments);
        // The most confident self-assessment wins: reading music fluently is
        // not cancelled out by only knowing what an interval is.
        $this->assertSame('advanced', $profile->musical_level);
        // Ten minutes a day is seventy a week, which rounds to one hour.
        $this->assertSame(1, $profile->weekly_practice_hours);
        $this->assertSame(
            ['Kulak egitimi gelistirme', 'Sinav hazirligi'],
            $profile->learning_goals,
        );
    }

    public function test_the_answers_fill_the_survey_questions_they_match(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/onboarding', $this->answers())
            ->assertOk()
            ->assertJsonPath('data.answers_recorded', 5);

        $this->assertSame('3', $this->answerTo($user, 'interval_recognition'));
        $this->assertSame('3', $this->answerTo($user, 'ear_training_level'));
        $this->assertSame('Akici nota okuyabiliyorum', $this->answerTo($user, 'sight_reading'));
        $this->assertSame('1-3 saat', $this->answerTo($user, 'weekly_study_time'));
        $this->assertSame(
            ['Kulak egitimi gelistirme', 'Sinav hazirligi'],
            json_decode($this->answerTo($user, 'learning_objectives'), true),
        );
    }

    /**
     * The app stores an answer for five topics and asks about two. The other
     * three arrive absent, and a survey answer invented for them would be a
     * claim the learner never made.
     */
    public function test_a_topic_that_was_not_asked_is_left_blank(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/onboarding', $this->answers())->assertOk();

        $this->assertNull($this->answerTo($user, 'chord_recognition'));
        $this->assertNull($this->answerTo($user, 'rhythm_perception'));
    }

    /** Nothing in the flow asks these, so nothing in the flow may overwrite them. */
    public function test_it_leaves_alone_what_the_app_never_asked(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => 'Been playing since school.',
            'interests' => ['Jazz'],
            'education_status' => 'music_school',
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/onboarding', $this->answers())->assertOk();

        $profile = $user->fresh()->profile;

        $this->assertSame('Been playing since school.', $profile->bio);
        $this->assertSame(['Jazz'], $profile->interests);
        $this->assertSame('music_school', $profile->education_status);
        // …while what it did ask about is now filled in.
        $this->assertSame('Piano', $profile->primary_instrument);
    }

    /** The answers are the most recent thing the learner said, so they win. */
    public function test_the_answers_replace_an_earlier_survey_response(): void
    {
        $user = User::factory()->create();
        $question = QuestionnaireQuestion::where('key', 'sight_reading')->firstOrFail();

        QuestionnaireResponse::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer_value' => 'Nota okuyamiyorum',
        ]);

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/me/onboarding', $this->answers())->assertOk();

        $this->assertSame('Akici nota okuyabiliyorum', $this->answerTo($user, 'sight_reading'));
        $this->assertSame(
            1,
            QuestionnaireResponse::where('user_id', $user->id)
                ->where('question_id', $question->id)
                ->count(),
        );
    }

    public function test_someone_who_plays_nothing_gets_no_instrument(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/onboarding', $this->answers([
            'instruments' => ['nothing'],
            'goals' => ['rhythm'],
            'topics' => ['intervals' => 'none', 'notation' => 'none'],
            'minutes_per_day' => 5,
        ]))->assertOk();

        $profile = $user->fresh()->profile;

        $this->assertNull($profile->primary_instrument);
        $this->assertSame('beginner', $profile->musical_level);
        // Five minutes a day is under an hour a week, and `rhythm` is the one
        // goal the web survey has no option for — so no objectives are written.
        $this->assertSame('1 saatten az', $this->answerTo($user, 'weekly_study_time'));
        $this->assertNull($this->answerTo($user, 'learning_objectives'));
        $this->assertNull($profile->learning_goals);
    }

    public function test_it_rejects_answers_it_does_not_recognise(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/me/onboarding', $this->answers(['goals' => ['conducting']]))
            ->assertStatus(422);

        $this->postJson('/api/v1/me/onboarding', $this->answers([
            'topics' => ['intervals' => 'expert'],
        ]))->assertStatus(422);

        $this->postJson('/api/v1/me/onboarding', $this->answers(['minutes_per_day' => 0]))
            ->assertStatus(422);
    }

    public function test_it_needs_a_signed_in_account(): void
    {
        $this->postJson('/api/v1/me/onboarding', $this->answers())->assertStatus(401);
    }

    /**
     * The mapping addresses the survey options by position, so that fixing the
     * ASCII Turkish the seeder wrote does not silently break it. Reordering
     * them would, which is what this notices.
     */
    public function test_the_survey_options_are_still_in_the_order_the_mapping_expects(): void
    {
        $this->assertSame(
            ['Nota okuyamiyorum', 'Basit melodi okuyabiliyorum', 'Orta zorlukta parcalar', 'Akici nota okuyabiliyorum'],
            QuestionnaireQuestion::where('key', 'sight_reading')->firstOrFail()->options,
        );

        $this->assertSame(
            [
                'Kulak egitimi gelistirme', 'Nota okuma', 'Muzik teorisi', 'Sinav hazirligi',
                'Enstruman performansi', 'Kompozisyon / duzenleme', 'Hobi olarak muzik',
            ],
            QuestionnaireQuestion::where('key', 'learning_objectives')->firstOrFail()->options,
        );

        $this->assertSame(
            ['1 saatten az', '1-3 saat', '3-5 saat', '5-10 saat', '10 saatten fazla'],
            QuestionnaireQuestion::where('key', 'weekly_study_time')->firstOrFail()->options,
        );
    }
}
