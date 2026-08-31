<?php

namespace Tests\Feature\Api;

use App\Models\ChordPractice;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\Practice\PracticeAnswerGrader;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The API's language resolution, which had no test and did not work.
 *
 * Two independent faults put every response in English: `locales.supported` is
 * a plain list but was read with array_keys(), and the user rung read the
 * default (session) guard from inside the session-less api group. Either one
 * alone is enough to swallow the whole feature silently, which is why these
 * assert the resolved locale directly rather than some string downstream of it.
 *
 * The second half of the file covers what the resolved locale is actually for:
 * translated validation errors, catalog names and answer labels. Those assert
 * against real Spanish and Turkish strings on purpose — asserting merely that
 * "something changed" would still pass if the value were the wrong language.
 */
class LocaleApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A sign-in that succeeds, for the cases that need a real anonymous
     * request. Every other route sits behind auth:sanctum, and asserting a
     * locale on the way out of a 401 reads like a mistake even when it works.
     */
    private function credentials(): array
    {
        User::factory()->create([
            'email' => 'header@example.com',
            'password' => Hash::make('password123!'),
        ]);

        return [
            'email' => 'header@example.com',
            'password' => 'password123!',
            'device_name' => 'Pixel',
        ];
    }

    /**
     * The header wins over the account column, which is the opposite of the web
     * twin and is the point of this middleware.
     *
     * The app sends Accept-Language explicitly on every request because it
     * knows what language it is rendering. Its language picker ships behind
     * __DEV__, so a member who signed up on the Turkish website would otherwise
     * read Turkish options under English chrome.
     */
    public function test_the_header_wins_over_the_account_locale(): void
    {
        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));

        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertHeader('Content-Language', 'en');

        $this->assertSame('en', app()->getLocale());
    }

    /**
     * With nothing offered, the account's own language is the fallback.
     *
     * Symfony's test request factory injects Accept-Language: en-us,en;q=0.5
     * unless told otherwise, so "sent no header" has to be written as an empty
     * one. A real caller that omits it reaches the same branch.
     */
    public function test_the_account_locale_is_used_when_no_header_is_sent(): void
    {
        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));

        $this->withHeader('Accept-Language', '')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertHeader('Content-Language', 'tr');

        $this->assertSame('tr', app()->getLocale());
    }

    /** An unsupported column value must not win over a usable default. */
    public function test_an_unreadable_account_locale_falls_back_to_the_app_default(): void
    {
        // Accounts predating the validation rule can hold anything varchar(10)
        // accepts, and users.locale defaults to 'tr' at the column level.
        Sanctum::actingAs(User::factory()->create(['locale' => 'en-US']));

        $this->withHeader('Accept-Language', '')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        $this->assertSame(config('app.locale'), app()->getLocale());
    }

    public function test_the_header_is_used_when_the_request_is_anonymous(): void
    {
        $this->withHeader('Accept-Language', 'tr-TR, tr;q=0.9, en;q=0.5')
            ->postJson('/api/v1/auth/login', $this->credentials())
            ->assertOk();

        $this->assertSame('tr', app()->getLocale());
    }

    public function test_the_header_is_read_by_weight_not_by_position(): void
    {
        $this->withHeader('Accept-Language', 'en-US;q=0.5, tr;q=0.9')
            ->postJson('/api/v1/auth/login', $this->credentials())
            ->assertOk();

        $this->assertSame('tr', app()->getLocale());
    }

    public function test_an_unsupported_language_falls_back_to_the_app_default(): void
    {
        $this->withHeader('Accept-Language', 'ja-JP, ja;q=0.9')
            ->postJson('/api/v1/auth/login', $this->credentials())
            ->assertOk();

        $this->assertSame(config('app.locale'), app()->getLocale());
    }

    public function test_the_resolved_locale_is_reported_back_in_content_language(): void
    {
        // The client reads this into serverLanguage(): what it asked for is not
        // always what it got, because an unsupported ask falls back silently.
        Sanctum::actingAs(User::factory()->create(['locale' => 'de']));

        // Asked for Japanese, which is not supported; the account column is the
        // fallback, so the answer is German and the header says so.
        $this->withHeader('Accept-Language', 'ja-JP, ja;q=0.9')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertHeader('Content-Language', 'de');
    }

    public function test_content_language_is_set_on_an_anonymous_request_too(): void
    {
        $this->withHeader('Accept-Language', 'it')
            ->postJson('/api/v1/auth/login', $this->credentials())
            ->assertOk()
            ->assertHeader('Content-Language', 'it');
    }

    public function test_profile_update_accepts_a_supported_locale(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/me/profile', ['locale' => 'tr'])
            ->assertOk()
            ->assertJsonPath('data.locale', 'tr');

        $this->assertSame('tr', $user->fresh()->locale);
    }

    public function test_profile_update_rejects_an_unsupported_locale(): void
    {
        Sanctum::actingAs(User::factory()->create(['locale' => 'en']));

        $this->putJson('/api/v1/me/profile', ['locale' => 'xx'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('locale');
    }

    public function test_profile_update_rejects_a_regional_locale_tag(): void
    {
        // The column is the bare two-letter form everywhere downstream —
        // config/locales.php, SetLocale, and CoachPlanService::LOCALES.
        Sanctum::actingAs(User::factory()->create(['locale' => 'en']));

        $this->putJson('/api/v1/me/profile', ['locale' => 'en-US'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('locale');
    }

    public function test_register_stores_a_supported_locale(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'device_name' => 'iPhone 15',
            'locale' => 'tr',
        ])->assertCreated()->assertJsonPath('data.user.locale', 'tr');

        $this->assertDatabaseHas('users', ['email' => 'ada@example.com', 'locale' => 'tr']);
    }

    public function test_register_rejects_an_unsupported_locale(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'device_name' => 'iPhone 15',
            'locale' => 'en-US',
        ])->assertStatus(422)->assertJsonValidationErrors('locale');
    }

    public function test_register_without_a_locale_uses_the_app_default(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'device_name' => 'iPhone 15',
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'locale' => config('app.locale'),
        ]);
    }

    /**
     * Every supported locale must resolve validation.php, auth.php and
     * passwords.php. Five of the seven had no such files at all and silently
     * served the framework's English, which is invisible from inside the app —
     * __() returns a real sentence either way.
     */
    public function test_validation_errors_are_translated_in_every_supported_locale(): void
    {
        foreach (config('locales.supported') as $locale) {
            $message = $this->withHeader('Accept-Language', $locale)
                ->postJson('/api/v1/auth/register', ['device_name' => 'Pixel'])
                ->assertStatus(422)
                ->json('errors.email.0');

            $this->assertIsString($message);

            if ($locale === 'en') {
                $this->assertStringContainsString('required', $message);

                continue;
            }

            $this->assertStringNotContainsString(
                'required',
                $message,
                "The {$locale} validation message is still the English fallback.",
            );
        }
    }

    public function test_a_failed_login_is_reported_in_the_requested_language(): void
    {
        $this->credentials();

        $this->withHeader('Accept-Language', 'de')
            ->postJson('/api/v1/auth/login', [
                'email' => 'header@example.com',
                'password' => 'wrong-password',
                'device_name' => 'Pixel',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', trans('auth.failed', [], 'de'));
    }

    public function test_practice_type_names_are_translated(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));
        $this->withHeader('Accept-Language', 'tr');

        $types = collect($this->getJson('/api/v1/catalog/practice-types')->assertOk()->json('data'))
            ->keyBy('slug');

        // The slug is the contract and stays canonical; only the name moves.
        $this->assertSame('single-note-practice', $types['single-note-practice']['slug']);
        $this->assertNotSame(
            trans('app.exercises.single_note', [], 'en'),
            trans('app.exercises.single_note', [], 'tr'),
            'There is no Turkish translation for this name, so the test proves nothing.',
        );
        $this->assertSame(
            trans('app.exercises.single_note', [], 'tr'),
            $types['single-note-practice']['name'],
        );
        $this->assertSame(
            trans('app.exercises.single_note_desc', [], 'tr'),
            $types['single-note-practice']['description'],
        );
    }

    public function test_learning_path_category_names_are_translated(): void
    {
        $category = ExerciseCategory::create([
            'slug' => 'melodic-dictation',
            'name' => 'Melodic Dictation',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));
        $this->withHeader('Accept-Language', 'tr');

        $translated = trans('app.catalog.categories.melodic-dictation', [], 'tr');

        // trans() falls back to fallback_locale for a missing key, so asserting
        // only that the response equals trans(..., 'tr') would still pass if the
        // Turkish key were absent — both sides would be the English string.
        $this->assertNotSame(
            trans('app.catalog.categories.melodic-dictation', [], 'en'),
            $translated,
            'There is no Turkish translation for this category, so the test proves nothing.',
        );

        $this->getJson('/api/v1/catalog/learning-path')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $category->slug)
            ->assertJsonPath('data.0.name', $translated);
    }

    /**
     * Categories are seeded rows, but admins can add more and the fixtures in
     * the other API tests invent their own. An unknown slug must keep the name
     * it has in the column rather than losing it to a missing lang key.
     */
    public function test_an_unknown_category_slug_keeps_its_stored_name(): void
    {
        ExerciseCategory::create([
            'slug' => 'brand-new-category',
            'name' => 'Brand New Category',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));
        $this->withHeader('Accept-Language', 'tr');

        $this->getJson('/api/v1/catalog/learning-path')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Brand New Category');
    }

    public function test_a_lesson_description_is_localized_like_its_title(): void
    {
        $category = ExerciseCategory::create([
            'slug' => 'intervals',
            'name' => 'Intervals',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        LearningPathExercise::create([
            'category_id' => $category->id,
            'slug' => 'thirds',
            'title' => 'Thirds',
            'description' => 'Hear the difference.',
            'translations' => ['tr' => ['title' => 'Üçlüler', 'description' => 'Farkı duy.']],
            'tags' => [],
            'skills_trained' => [],
            'level' => 'beginner',
            'sort_order' => 1,
            'estimated_duration_minutes' => 5,
            'is_active' => true,
            'config_json' => ['practice_type' => 'melodic-interval-practice'],
        ]);

        Sanctum::actingAs(User::factory()->create(['locale' => 'tr']));
        $this->withHeader('Accept-Language', 'tr');

        $this->getJson('/api/v1/catalog/learning-path/thirds')
            ->assertOk()
            ->assertJsonPath('data.title', 'Üçlüler')
            // getLocalizedDescription() already existed and was never called.
            ->assertJsonPath('data.description', 'Farkı duy.');
    }

    /**
     * The one that matters for correctness rather than presentation: the label
     * is translated, the value the client submits is not. PracticeAnswerGrader
     * compares the value, so a translated value would fail every answer in six
     * of the seven languages.
     */
    public function test_chord_option_labels_are_translated_but_values_stay_canonical(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'es', 'plan' => 'premium']));
        $this->withHeader('Accept-Language', 'es');

        $options = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 3,
        ])->assertCreated()->json('data.questions.0.answer.options');

        $this->assertNotEmpty($options);

        $canonical = array_keys(ChordPractice::chordIntervals());

        foreach ($options as $option) {
            $this->assertContains(
                $option['value'],
                $canonical,
                'The submitted value must stay a canonical English chord name.',
            );
            $this->assertSame(music_label($option['value'], 'chord', 'es'), $option['label']);
        }
    }

    public function test_interval_option_labels_are_translated(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'es', 'plan' => 'premium']));
        $this->withHeader('Accept-Language', 'es');

        $options = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'melodic-interval-practice',
            'question_count' => 3,
        ])->assertCreated()->json('data.questions.0.answer.options');

        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertSame(music_label($option['value'], 'interval', 'es'), $option['label']);
        }

        // A real translation, not the canonical name echoed back.
        $this->assertNotSame(
            array_column($options, 'value'),
            array_column($options, 'label'),
        );
    }

    /**
     * Interval construction asks the learner to BUILD an interval, so the
     * interval name is the prompt rather than an option — and its options are
     * note names. It is the most visible interval name in the app and was the
     * one place the canonical English leaked through to every language.
     */
    public function test_the_interval_construction_prompt_is_labelled(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'es', 'plan' => 'premium']));
        $this->withHeader('Accept-Language', 'es');

        $meta = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'interval-construction-practice',
            'question_count' => 3,
        ])->assertCreated()->json('data.questions.0.meta');

        $this->assertArrayHasKey('interval', $meta);
        $this->assertSame(music_label($meta['interval'], 'interval', 'es'), $meta['interval_label']);
        $this->assertNotSame($meta['interval'], $meta['interval_label']);
    }

    public function test_direction_option_labels_are_translated(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'tr', 'plan' => 'premium']));
        $this->withHeader('Accept-Language', 'tr');

        $options = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'interval-direction-practice',
            'question_count' => 3,
        ])->assertCreated()->json('data.questions.0.answer.options');

        $this->assertSame(['ascending', 'descending'], array_column($options, 'value'));
        $this->assertSame(
            [trans('app.exercises.ascending', [], 'tr'), trans('app.exercises.descending', [], 'tr')],
            array_column($options, 'label'),
        );
    }

    /**
     * End to end: a session run entirely in Spanish still grades on the
     * canonical value. This is the regression that a translated `value` would
     * cause, and it would not show up in any of the presentation tests above.
     */
    public function test_answers_are_still_graded_correctly_in_a_translated_session(): void
    {
        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['locale' => 'es', 'plan' => 'premium']));
        $this->withHeader('Accept-Language', 'es');

        $uuid = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 3,
        ])->assertCreated()->json('data.session.uuid');

        $session = PracticeSession::where('uuid', $uuid)->firstOrFail();
        $correct = app(PracticeAnswerGrader::class)
            ->correctAnswerFor($session->questionAt(0), 'chord-practice');

        $this->withHeader('Accept-Language', 'es')
            ->postJson("/api/v1/sessions/{$uuid}/answers", ['index' => 0, 'answer' => $correct])
            ->assertOk()
            ->assertJsonPath('data.is_correct', true);
    }
}
