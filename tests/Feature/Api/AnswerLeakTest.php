<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\Practice\PracticeCatalog;
use Database\Seeders\NewPracticeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The API resolves playable pitches server-side precisely so the correct
 * answer never has to be shipped to the client. This locks that in.
 *
 * Rhythm and melodic dictation are the two documented exceptions: the client
 * cannot play the question without receiving the rhythm/melody, exactly as on
 * the web today.
 */
class AnswerLeakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NewPracticeTypeSeeder::class);
        app(PracticeCatalog::class)->flushCache();
        Sanctum::actingAs(User::factory()->create(['plan' => 'premium']));
    }

    /** slug => the answer-bearing keys that must not appear anywhere in the payload */
    public static function leakCases(): array
    {
        return [
            'single note' => ['single-note-practice', ['target']],
            'melodic interval' => ['melodic-interval-practice', ['interval']],
            'harmonic interval' => ['harmonic-interval-practice', ['interval']],
            'interval direction' => ['interval-direction-practice', ['direction']],
            'interval comparison' => ['interval-comparison-practice', ['target']],
            'chord' => ['chord-practice', ['chord_type']],
            'scale' => ['scale-practice', ['scale_type']],
            'interval construction' => ['interval-construction-practice', ['note2']],
        ];
    }

    #[DataProvider('leakCases')]
    public function test_the_answer_field_is_absent_from_the_question_payload(string $slug, array $forbidden): void
    {
        $questions = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => $slug,
            'question_count' => 5,
        ])->assertCreated()->json('data.questions');

        foreach ($questions as $i => $question) {
            $flat = $this->flattenKeys($question);

            foreach ($forbidden as $key) {
                $this->assertNotContains(
                    $key,
                    $flat,
                    "{$slug} question {$i} leaked the answer field '{$key}'."
                );
            }
        }
    }

    public function test_chord_questions_still_carry_the_pitches_needed_to_play_them(): void
    {
        $questions = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 3,
        ])->json('data.questions');

        foreach ($questions as $q) {
            $this->assertNotEmpty($q['audio']['notes'], 'Chord question has no playable pitches.');
            $this->assertContains($q['audio']['playback'], ['simultaneous', 'arpeggio']);
        }
    }

    public function test_scale_questions_carry_an_ordered_pitch_sequence(): void
    {
        $questions = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'scale-practice',
            'question_count' => 3,
        ])->json('data.questions');

        foreach ($questions as $q) {
            $this->assertGreaterThanOrEqual(5, count($q['audio']['notes']));
            $this->assertSame('sequential', $q['audio']['playback']);
        }
    }

    public function test_interval_questions_carry_exactly_two_pitches(): void
    {
        foreach (['melodic-interval-practice', 'harmonic-interval-practice'] as $slug) {
            $questions = $this->postJson('/api/v1/sessions', [
                'source' => 'studio',
                'practice_type' => $slug,
                'question_count' => 3,
            ])->json('data.questions');

            foreach ($questions as $q) {
                $this->assertCount(2, $q['audio']['notes'], "{$slug} should sound two pitches.");
            }
        }
    }

    public function test_the_session_row_never_serializes_its_questions(): void
    {
        $session = $this->postJson('/api/v1/sessions', [
            'source' => 'studio',
            'practice_type' => 'chord-practice',
            'question_count' => 3,
        ])->json('data.session');

        $this->assertArrayNotHasKey('questions_json', $session);
        $this->assertArrayNotHasKey('answers_json', $session);
        $this->assertArrayNotHasKey('id', $session);
    }

    /** Every key appearing anywhere in a nested array. */
    private function flattenKeys(array $data): array
    {
        $keys = [];

        array_walk_recursive($data, function () {});

        $walk = function (array $node) use (&$walk, &$keys) {
            foreach ($node as $key => $value) {
                if (is_string($key)) {
                    $keys[] = $key;
                }
                if (is_array($value)) {
                    $walk($value);
                }
            }
        };

        $walk($data);

        return $keys;
    }
}
