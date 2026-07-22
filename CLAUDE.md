# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Laravel 12, PHP 8.2+, Livewire v3, Alpine.js, Tailwind CSS, Vite. SQLite in tests, MySQL in production. Auth via Laravel Breeze + Google Socialite. OpenAI via `openai-php/client`. Activity logging via `spatie/laravel-activitylog`.

## Commands

```bash
# Full dev environment (server + queue + logs + vite hot-reload)
composer run dev

# Build assets
npm run build

# Run all tests (in-memory SQLite)
composer test
php artisan test tests/Feature/ProfileTest.php   # single file

# Lint
./vendor/bin/pint
```

## Architecture

### Practice page routing

All practice pages share one route: `GET /practice/{slug}` → `PageController::practiceView()`. That method loads all records from the relevant model class, then passes them to `resources/views/practice.blade.php`. That blade uses `@switch($slug)` to dispatch to the correct Livewire component:

```
slug → Livewire component
single-note-practice            → <livewire:practice-single-note>
melodic-interval-practice       → <livewire:practice-melodic-interval>
harmonic-interval-practice      → <livewire:practice-harmonic-interval>
interval-direction-practice     → <livewire:practice-interval-direction>
interval-comparison-practice    → <livewire:practice-interval-comparison>
interval-construction-practice  → <livewire:practice-interval-construction>
chord-practice                  → <livewire:practice-chord>
scale-practice                  → <livewire:practice-scale>
rhythm-practice                 → <livewire:practice-rhythm>
melodic-dictation               → <livewire:practice-melodic-dictation>
```

### Four practice entry points

1. **Direct navigation** — `/practice/{slug}` loads all DB records, no session. Livewire `mount()` calls `$this->serializePractices($practices)`.

2. **Learning Path** — `/learn-exercise/{slug}` (detail page) → POST `start` → `LearningPathController::start()` generates questions via `LearningPathQuestionGenerator`, stores them in `session('learning_path_session')`, redirects to `/practice/{slug}`. `PageController::practiceView()` detects the session and passes generated questions instead of DB records.

3. **Exercise Setup Studio** — `/exercise-setup` → POST `launch` → `ExerciseSetupController::launch()` stores config in `session('exercise_settings')`, clears any stale `learning_path_session`, redirects to `/practice/{slug}`. Each Livewire component's `mount()` reads/forgets `exercise_settings`, calls `LearningPathQuestionGenerator`, stores results in `session('exercise_practice_session')`.

4. **AI Assisted Exercises** — `/ai-exercises` → POST `generate` → `AIController::generatePractices()`. Generic path: questions go to `session('ai_practice_questions')` → `/practice-ai` → `practice-mixed` view. Special case: when **only** `melodic-dictation` is selected, the controller stores `session('ai_dictation_settings')` (question_count + difficulty) and redirects to `/practice-ai/melodic-dictation`, which renders `practice-ai-dictation.blade.php` + `PracticeAiMelodicDictation`. That component **extends** `PracticeMelodicDictation` (same engine: beat-pattern rhythm + `TonalMelodyGenerator`), auto-derives key/time-signature/tempo/note-value pool from the difficulty (easy→beginner, medium/adaptive→intermediate, hard→advanced), and stores `session('exercise_practice_session')` so answer checking is shared. Its blade `practice-ai-melodic-dictation.blade.php` is a re-skinned (indigo/AI theme) clone of `practice-melodic-dictation.blade.php` — keep their JS/structure in sync when editing either.

### Answer checking priority in PracticeController::checkAnswer()

Checks in this order — **order matters**:
1. `exercise_practice_session` (exercise-setup flow) — checked **first** to prevent stale LP session from intercepting
2. `learning_path_session` (LP flow)
3. Slug-based DB lookup (`chord-practice`, `scale-practice`, etc. via `$slugModels`)
4. Integer `practice_id` DB lookup (legacy types 1–6)

### LearningPathQuestionGenerator (`app/Services/LearningPathQuestionGenerator.php`)

The single service that generates all practice questions programmatically. Takes a `LearningPathExercise` model (or an ad-hoc instance with `config_json` set) and a `$questionCount`. Returns a `Collection` of unsaved Eloquent models. Callers assign sequential IDs: `$q->id = $i + 1`.

Key methods:
- `generate(LearningPathExercise, int): Collection`
- `serializeForSession(Collection): array` — calls `getAttributes()`, decodes JSON-cast array fields to plain arrays
- `reconstructFromSession(array, string): Collection` — rebuilds models with 1-based IDs
- `getAnswerFromSessionQuestion(array, string): string` — returns the correct answer field for a given practice type slug

`config_json` keys per practice type:

| type | required keys |
|------|--------------|
| `melodic-interval-practice` / `harmonic-interval-practice` | `allowed_intervals` (full names), `allowed_notes`, `octave_range` |
| `interval-direction-practice` | `allowed_intervals_semitones` (ints 1–12), `allowed_notes`, `octave`, `clef` |
| `interval-construction-practice` | `allowed_intervals`, `allowed_root_notes`, `octave` **or** `clef`, `direction` (asc/desc/mixed), optional `distractor_mode: near` — answer `options` + `clef` are stored on each question and survive session serialization |
| `interval-comparison-practice` | `allowed_interval_pairs` (array of `['C,D','C,E']` pairs), `octave`, `clef` |
| `chord-practice` | `allowed_chord_types`, `allowed_root_notes`, `voicing`, `include_inversions`, `distractor_pool` (empty = the lesson's own types), `octave` **or** `clef`, optional `inversion_values` (e.g. `[1]` for a first-inversion-only lesson — overrides `include_inversions`) |
| `scale-practice` | `allowed_scale_types`, `allowed_root_notes`, `direction` (ascending/descending/both — `both` mixes per-question directions; melodic minor stays ascending in `both` mode), `octave` **or** `clef`, `distractor_pool` |
| `rhythm-practice` | `time_signatures` (several allowed — questions mix meters), `allowed_note_values` (rest tokens add rest cells; `eighth_rest` always pairs with an eighth inside the beat, never `[8r,8r]`), `tempo_range`, `bars`, optional `rhythm_difficulty` (easy/medium/hard distractor level; LP maps beginner→easy, advanced→hard), optional `exclude_cells` (comma-joined cell token sequences to drop, e.g. `eighth,quarter,eighth` keeps syncopation out of a focused lesson), optional `include_rests` (Studio flow: inject exactly one rest). Bars always open on a sounded note. `tests/Unit/RhythmGeneratorTest.php` enforces the 16-lesson LP rhythm curriculum invariants |
| `melodic-dictation` | `key_signatures` (**major roots only** — minor lessons pass the relative major root + `mode: minor`, never `Am`/`Dm` pseudo-roots), `mode`, `difficulty` (TonalMelodyGenerator motion rules), `accidentals` (`none`/`harmonic`/`melodic`/`auto` — pins the accidental treatment for focused lessons), `include_rhythm: true` + `time_signature` + `allowed_note_values` + `bars` (DictationRhythmService beat-pattern rhythm — bar math always exact), `note_pool` (focused register; **single-key lessons only**, pool must be diatonic to the key) **or** no pool → clef range via `contextForKey` (required for multi-key lessons), `tempo_range`. Questions store `tonic` + `mode` (blade header reads them). Legacy: `melody_length` without `include_rhythm` (AI mixed flow) still yields pitch-only questions. `tests/Unit/MelodicDictationGeneratorTest.php` enforces the 16-lesson LP dictation curriculum invariants |
| `single-note-practice` | `allowed_notes` (spellings preserved — flats stay flats; answer checking accepts enharmonic equivalents everywhere since the piano answer keyboard emits sharp names), `octave_range` **or** `clef`, `distractor_count`, optional `answer_mode` (`note-names` = labeled answer keys, `keyboard` = unlabeled; rides on each question so the blade honours it in the LP flow). Reference note: a natural with a different letter inside the clef range (neighbouring octave at range edges, e.g. C4 in bass). `tests/Unit/SingleNoteGeneratorTest.php` enforces the 15-lesson LP curriculum invariants |

Pitch range rule: for every pitched type, passing `clef` (instead of a hardcoded `octave`/`octave_range`) makes the generator place all notes inside `MusicTheoryService::CLEF_RANGES` — site standard: treble **G3–G5**, bass **C2–C4**, alto **C3–C5**. Explicit `octave`/`octave_range` wins over `clef` when both are present (legacy LP seed configs). Exercise Setup Studio and Teacher Assignments (`TeacherAssignmentConfigFactory`) both use the clef-driven path. Chord/scale type names must be the canonical `chordIntervals()`/`scaleIntervals()` keys (`Major`, `Natural Minor`, `Dominant 7th`, …) — lowercase slugs silently fall back to Major intervals. For scales and chords, the generator normalizes legacy slugs (`natural-minor`, `dominant7`, `half-diminished7`, …) to canonical names via `canonicalScaleType()`/`canonicalChordType()`. Acoustic twins (Aeolian = Natural Minor, Ionian = Major) must never share one answer pool — the LP scale curriculum keeps one label per sound per lesson (`tests/Unit/ScaleGeneratorTest.php` enforces this); for chords, `chordDistractors()` dedupes options by interval set automatically (`tests/Unit/ChordGeneratorTest.php`). Chord questions carry their `other_options` (and `clef`) through session serialization; the practice-chord blade renders those instead of drawing from the full vocabulary.

Interval name abbreviations (`m2`, `M2`, `TT`, `8ve`, etc.) from exercise-setup are **not** understood by the generator — they must be mapped to full names (`Minor 2nd`, `Major 2nd`, `Tritone`, `Perfect Octave`) in the Livewire component before passing to the generator.

### TonalMelodyGenerator (`app/Services/TonalMelodyGenerator.php`)

The single canonical source of melodic-dictation melodies. Both `PracticeMelodicDictation::mount()` (Exercise Setup flow) and `LearningPathQuestionGenerator::generateMelodicDictation()` (LP flow) generate pitches through it — never generate dictation melodies inline. It enforces tonal/pedagogical rules per difficulty (`beginner`/`intermediate`/`advanced`): diatonic note pool in the selected key, start on a tonic-triad degree, level-based ending (beginner always tonic), ≥70% steps/thirds, per-level leap size/count caps, no consecutive leaps, contrary-motion leap resolution, and per-level range caps (beginner: M6, intermediate: octave, advanced: M10). Beginner additionally allows sparse repeated notes (never two in a row) — required, not cosmetic: steps-only motion in a bipartite scale graph would otherwise deadlock narrow-pool lessons on start/end tonic parity. `applyAccidentals()` takes an optional `$flavor` (`none`/`harmonic`/`melodic`/`auto`): `none` = fully diatonic, `harmonic`/`melodic` pin the minor treatment for focused LP lessons, `auto` = Studio difficulty-based mix. `relativeMinorRoot()` maps a major key root to its relative minor tonic. `generateMelody()` retries a weighted random walk until `validateMelody()` passes, then falls back to a stepwise line that is valid by construction. Rules are covered by `tests/Unit/TonalMelodyGeneratorTest.php` (run with `./vendor/bin/phpunit tests/Unit/TonalMelodyGeneratorTest.php`).

### HandlesPracticeData trait (`app/Livewire/Concerns/HandlesPracticeData.php`)

All Livewire practice components use this. Stores `$practiceDataArray` as plain PHP arrays (never Eloquent models) to survive Livewire's serialization. Key methods:
- `serializePractices($practices): array`
- `serializeOnePractice($practice): array` — calls `getAttributes()`, decodes JSON-encoded array fields (anything starting with `[`)
- `buildModelFromData(string $class, ?array $data): ?object` — rebuilds model from serialized array
- `getCurrentPracticeData(): ?array`

### Livewire component mount() pattern

Every `Practice*.php` component follows:

```php
public function mount($practices) {
    $settings = session('exercise_settings', []);
    session()->forget('exercise_settings');

    if (!empty($settings)) {
        // Generate via LearningPathQuestionGenerator
        // session(['exercise_practice_session' => [...]])
        // $this->practiceDataArray = ...
    } else {
        $this->practiceDataArray = $this->serializePractices($practices);
    }
}
```

For interval types (melodic, harmonic, construction), `_options` set via `$q->setRelation('_options', ...)` in the generator does **not** appear in `getAttributes()` — options must be computed manually and stored directly in the serialized data array as `$data['options']`.

For `single-note-practice`, the blade does `explode(',', $currentPractice->other_options)` to render all answer buttons — `other_options` must be a comma-separated string that **includes the correct answer** (the generator only stores distractors; callers must prepend the target).

### User roles and plans

`User->role`: `user`, `teacher`, `school`, `admin`. Enforced by `AdminMiddleware`, `TeacherMiddleware`, `SchoolMiddleware`.

`User->plan`: `free`, `premium`. Limits defined in `config/plans.php`, accessed via `$user->getPlanLimit('feature_key')`. Free users: 3 exercises/day per type (`DailyExerciseCount::incrementCount()`), 3 saved templates, no AI mode. `-1` means unlimited.

### Adding a new practice type

1. Migration + Model in `app/Models/`
2. Livewire component in `app/Livewire/Practice*.php` (follow mount pattern above)
3. Blade in `resources/views/livewire/practice-*.blade.php`
4. Register in `PageController::$practiceMap`
5. Add `@case` in `resources/views/practice.blade.php`
6. Add to `LearningPathQuestionGenerator::generate()` match and `getAnswerFromSessionQuestion()` match
7. Add to `ExerciseSetupController::EXERCISE_SLUGS` and `slugToPracticeId()`
8. Add to `PracticeController::$slugModels` / `$slugTargetFields`

### i18n

15 locales in `resources/lang/` (NOT the Laravel-11-default root `lang/` — new lang files must go under `resources/lang/{locale}/`). `SetLocale` middleware applies `app()->setLocale()` from `User->locale`. `LearningPathExercise` has a `translations` JSON column; use `$exercise->getLocalizedTitle()`. Language switched via `POST /language/switch`.

### Email Center (Amazon SES)

Admin module at `/admin/email-center` (sidebar: Email Center + Support Inbox). All outbound mail goes through Amazon SES (eu-central-1, domain `harmoniva.app` verified, config set `harmoniva-email-events` publishes send/delivery/open/click/bounce/complaint events to SNS → `POST /webhooks/aws/ses/events`, validated by `SnsMessageValidator`).

Key pieces (all under `App\Services\EmailCenter`):
- `EmailDispatchService::dispatch()` — the only entry point for queueing mail. Creates `email_messages` row (owns `tracking_token`), enforces suppression list + weekly frequency cap for marketing types (`campaign`, `automation`); transactional types only blocked by `hard_bounce` suppression.
- `SendEmailCenterMessage` job — rate-limited (`email-center-send` limiter, SystemSetting `email_send_rate`), renders via `TemplateRenderer` ({{var}} substitution, signed click-redirect links + UTM, open pixel, unsubscribe footer), sends via `Mail::mailer('ses')`, stores `ses_message_id` from the `X-SES-Message-ID` header.
- `SesEventProcessor` — idempotent (dedup_hash), updates message status/campaign counters, auto-suppresses hard bounces + complaints. Soft bounces surface in admin Suppressions screen for manual action.
- `SegmentBuilder` — campaign segment JSON → User query (always excludes unverified/suspended/restricted/suppressed).
- `AutomationEngine` — 6 standard automations keyed by `email_automations.key` (welcome, first_exercise_reminder, learning_path_reminder, weekly_progress, re_engagement, premium_upsell); all seeded disabled via `EmailCenterSeeder`.
- Scheduler (`routes/console.php`): queue:work every minute (no daemon worker on this server), `email:process-campaigns` every minute, `email:run-automations` every 15 min, `support:fetch-mail` every 5 min. Requires the user-level crontab entry running `php8.2 artisan schedule:run`.

Support Inbox: MX stays on this server (local Postfix/Dovecot). `SupportMailFetcher` polls `support@harmoniva.app` over IMAP (`webklex/php-imap`, creds in `.env` `SUPPORT_IMAP_*`), threads by Message-ID/References into `support_conversations`/`support_messages`; admin replies go out via SES from the support address with `In-Reply-To`/`References` headers.

Tracking endpoints (public, signed): `GET /email/open/{token}`, `GET /email/click/{token}?url=`, `GET|POST /email/unsubscribe/{token}` (RFC 8058 one-click). CSRF exempt: `webhooks/aws/ses/*`, `email/unsubscribe/*` (see `bootstrap/app.php`).

Tests: `tests/Feature/EmailCenterTest.php`.
