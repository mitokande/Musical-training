<?php

namespace App\Services\Onboarding;

use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The mobile app's onboarding answers, written into the records the website
 * keeps about a learner.
 *
 * The app asks five questions before anyone signs up — what they want to do,
 * what they play, whether they can hear an interval, whether they read music,
 * and how long a day they have. The website asks a longer version of the same
 * thing across `user_profiles` and the profile survey, and the AI coach reads
 * both before it writes a plan. Until this existed the two never met: someone
 * who signed up in the app had answered most of the survey and still arrived at
 * an empty profile, and got a blander plan for it.
 *
 * The mapping lives here rather than in the app on purpose. The survey's option
 * strings are rows in this database, so a seeder edit would otherwise only
 * reach a learner when they next updated the app, and the two surfaces would
 * disagree for as long as that took. The app sends its own vocabulary
 * (`theory`, `piano`, `fluent`) and this class decides what it means here.
 *
 * Two rules it holds to:
 *
 * - Only what was actually asked. The app's profile stores an answer for five
 *   topics but currently puts a question to the learner about two of them; the
 *   other three arrive as absent, not as `none`, and nothing is written for
 *   them. A fabricated "cannot recognise chords" is worse than the blank the
 *   website already has.
 * - Only what it knows. Fields the app has nothing to say about — `bio`,
 *   `interests`, `education_status`, and every survey question with no
 *   counterpart in the flow — are left untouched rather than nulled, so a
 *   learner who filled them in on the web keeps them.
 *
 * Within those, the app's answers win: they are the most recent thing the
 * learner said. The client is what stops that from becoming a nuisance — it
 * sends a given set of answers to a given account exactly once, so editing the
 * profile on the web is not undone by opening the app afterwards.
 */
class MobileOnboardingProfile
{
    /** The topics the app can send an answer for, coarsest answer first. */
    public const FAMILIARITY = ['none', 'heard', 'know', 'fluent'];

    /** Every goal the app offers, including `sing`, which it no longer shows. */
    public const GOALS = ['play-by-ear', 'sing', 'theory', 'exam', 'dictation', 'rhythm'];

    public const INSTRUMENTS = ['piano', 'guitar', 'voice', 'other', 'nothing'];

    public const TOPICS = ['notation', 'intervals', 'chords', 'scales', 'rhythm'];

    /**
     * What each instrument is called in `user_profiles.primary_instrument`.
     *
     * A free-text column with no vocabulary of its own — the web form is a text
     * input — so these are simply the names the AI coach prompt reads best.
     * `nothing` is not an instrument and produces no primary.
     */
    private const INSTRUMENT_NAMES = [
        'piano' => 'Piano',
        'guitar' => 'Guitar',
        'voice' => 'Voice',
        'other' => 'Other',
    ];

    /** Where a self-assessment lands on the profile's three-step level. */
    private const MUSICAL_LEVEL = [
        'none' => 'beginner',
        'heard' => 'beginner',
        'know' => 'intermediate',
        'fluent' => 'advanced',
    ];

    /**
     * A self-assessment as a 1-5 survey scale.
     *
     * `fluent` is a 5 rather than a 4 because the app's top answer is "I hear it
     * and say the name", which is the whole of what these questions ask about.
     */
    private const SCALE = ['none' => 1, 'heard' => 2, 'know' => 3, 'fluent' => 5];

    /**
     * Survey questions answered by a topic self-assessment, as `scale` values.
     *
     * `intervals` fills two: the app's interval question is also the closest
     * thing anyone has told us about their ear training in general, which is
     * what `ear_training_level` asks.
     *
     * `chords` and `rhythm` are listed although the flow does not ask them yet —
     * when it grows a card for either, the answer lands here with no change.
     */
    private const TOPIC_SCALES = [
        'intervals' => ['interval_recognition', 'ear_training_level'],
        'chords' => ['chord_recognition'],
        'rhythm' => ['rhythm_perception'],
    ];

    /**
     * Which `sight_reading` option each answer to "can you read sheet music?"
     * picks, as an index into the question's own `options`.
     *
     * By index and not by string: the stored options are the ASCII Turkish the
     * seeder wrote, they are the value the web form posts, and a spelling fix
     * there should not silently stop this mapping working. A reorder would, and
     * is what MobileOnboardingProfileTest guards against.
     *
     * The app offers three answers where the survey has four; nobody is placed
     * at "Orta zorlukta parcalar" because nobody was asked a question that
     * separates it from reading fluently.
     */
    private const SIGHT_READING = ['none' => 0, 'heard' => 0, 'know' => 1, 'fluent' => 3];

    /** Which `learning_objectives` option each goal ticks, by index. */
    private const OBJECTIVES = [
        'theory' => 0,          // Kulak egitimi gelistirme
        'dictation' => 1,       // Nota okuma
        'exam' => 3,            // Sinav hazirligi
        'play-by-ear' => 4,     // Enstruman performansi
        // `rhythm` and the retired `sing` have no option on the web survey, and
        // are dropped rather than approximated.
    ];

    /** `weekly_study_time` buckets, as an upper bound in minutes per week. */
    private const WEEKLY_STUDY_TIME = [60, 180, 300, 600];

    /** The active survey, keyed by question key. Read once per instance. */
    private ?Collection $questions = null;

    /**
     * Applies one set of answers to the account.
     *
     * @param  array{
     *     goals: list<string>,
     *     instruments: list<string>,
     *     topics?: array<string, string>,
     *     minutes_per_day: int,
     *     completed_at?: string|null,
     * }  $answers
     * @return array{profile_updated: bool, answers_recorded: int}
     */
    public function apply(User $user, array $answers): array
    {
        $attributes = $this->profileAttributes($answers);

        if ($attributes !== []) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $attributes);
        }

        return [
            'profile_updated' => $attributes !== [],
            'answers_recorded' => $this->recordSurvey($user, $answers),
        ];
    }

    /**
     * The `user_profiles` columns the answers speak to, and no others.
     *
     * An absent key is a column left as the learner last set it — `bio`,
     * `interests` and `education_status` are never in here, and neither is
     * `primary_instrument` for someone who plays nothing.
     *
     * @return array<string, mixed>
     */
    private function profileAttributes(array $answers): array
    {
        $attributes = [];

        $instruments = $this->instrumentNames($answers['instruments'] ?? []);

        if ($instruments !== []) {
            $attributes['primary_instrument'] = array_shift($instruments);
            $attributes['secondary_instruments'] = $instruments === [] ? null : $instruments;
        }

        if ($level = $this->musicalLevel($answers['topics'] ?? [])) {
            $attributes['musical_level'] = $level;
        }

        if ($minutes = (int) ($answers['minutes_per_day'] ?? 0)) {
            // Rounded up from zero: someone doing five minutes a day is doing
            // half an hour a week, and a 0 in the coach's prompt reads as
            // "does not practise" rather than "practises a little".
            $attributes['weekly_practice_hours'] = max(1, (int) round($minutes * 7 / 60));
        }

        if ($goals = $this->objectiveLabels($answers['goals'] ?? [])) {
            $attributes['learning_goals'] = $goals;
        }

        return $attributes;
    }

    /**
     * Writes the survey answers the onboarding questions cover.
     *
     * @return int how many questions were answered
     */
    private function recordSurvey(User $user, array $answers): int
    {
        $recorded = 0;

        foreach ($this->surveyAnswers($answers) as $key => $value) {
            $question = $this->questions()->get($key);

            if (! $question || $value === null) {
                continue;
            }

            QuestionnaireResponse::updateOrCreate(
                ['user_id' => $user->id, 'question_id' => $question->id],
                ['answer_value' => is_array($value) ? json_encode(array_values($value)) : (string) $value],
            );

            $recorded++;
        }

        return $recorded;
    }

    /**
     * Every survey answer the onboarding answers imply, keyed by question.
     *
     * Values are already in the shape the web form posts: an option string for
     * a single choice, a JSON-ready list for a multi, a 1-5 integer for a scale.
     *
     * @return array<string, string|int|list<string>|null>
     */
    private function surveyAnswers(array $answers): array
    {
        $topics = $answers['topics'] ?? [];
        $survey = [];

        foreach (self::TOPIC_SCALES as $topic => $keys) {
            if (! isset($topics[$topic], self::SCALE[$topics[$topic]])) {
                continue;
            }

            foreach ($keys as $key) {
                $survey[$key] = self::SCALE[$topics[$topic]];
            }
        }

        if (isset($topics['notation'], self::SIGHT_READING[$topics['notation']])) {
            $survey['sight_reading'] = $this->option('sight_reading', self::SIGHT_READING[$topics['notation']]);
        }

        if ($objectives = $this->objectiveLabels($answers['goals'] ?? [])) {
            $survey['learning_objectives'] = $objectives;
        }

        if ($minutes = (int) ($answers['minutes_per_day'] ?? 0)) {
            $survey['weekly_study_time'] = $this->option('weekly_study_time', $this->weeklyBucket($minutes * 7));
        }

        return $survey;
    }

    /**
     * The stored (untranslated) option at an index, which is what the survey
     * saves as the answer. Null when the question or the index is gone, which
     * simply means that answer is not recorded.
     */
    private function option(string $key, int $index): ?string
    {
        $question = $this->questions()->get($key);

        return $question === null ? null : ($question->options[$index] ?? null);
    }

    /** @return Collection<string, QuestionnaireQuestion> */
    private function questions(): Collection
    {
        return $this->questions ??= QuestionnaireQuestion::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('key');
    }

    /** @return list<string> */
    private function objectiveLabels(array $goals): array
    {
        $labels = [];

        foreach ($goals as $goal) {
            if (! isset(self::OBJECTIVES[$goal])) {
                continue;
            }

            if ($label = $this->option('learning_objectives', self::OBJECTIVES[$goal])) {
                $labels[] = $label;
            }
        }

        return array_values(array_unique($labels));
    }

    /** @return list<string> */
    private function instrumentNames(array $instruments): array
    {
        $names = [];

        foreach ($instruments as $instrument) {
            if (isset(self::INSTRUMENT_NAMES[$instrument])) {
                $names[] = self::INSTRUMENT_NAMES[$instrument];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * The level the self-assessments add up to.
     *
     * The most confident answer wins, the same way the app's own plan takes the
     * most demanding instrument: someone who reads music fluently but has never
     * named an interval is not a beginner, they are a beginner at one thing.
     */
    private function musicalLevel(array $topics): ?string
    {
        $best = null;

        foreach ($topics as $answer) {
            if (! isset(self::MUSICAL_LEVEL[$answer])) {
                continue;
            }

            $rank = array_search($answer, self::FAMILIARITY, true);

            if ($best === null || $rank > $best) {
                $best = $rank;
            }
        }

        return $best === null ? null : self::MUSICAL_LEVEL[self::FAMILIARITY[$best]];
    }

    /** Which `weekly_study_time` bucket a weekly minute count falls in. */
    private function weeklyBucket(int $minutes): int
    {
        foreach (self::WEEKLY_STUDY_TIME as $index => $ceiling) {
            if ($minutes < $ceiling) {
                return $index;
            }
        }

        return count(self::WEEKLY_STUDY_TIME);
    }
}
