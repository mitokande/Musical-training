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

### Three practice entry points

1. **Direct navigation** — `/practice/{slug}` loads all DB records, no session. Livewire `mount()` calls `$this->serializePractices($practices)`.

2. **Learning Path** — `/learn-exercise/{slug}` (detail page) → POST `start` → `LearningPathController::start()` generates questions via `LearningPathQuestionGenerator`, stores them in `session('learning_path_session')`, redirects to `/practice/{slug}`. `PageController::practiceView()` detects the session and passes generated questions instead of DB records.

3. **Exercise Setup Studio** — `/exercise-setup` → POST `launch` → `ExerciseSetupController::launch()` stores config in `session('exercise_settings')`, clears any stale `learning_path_session`, redirects to `/practice/{slug}`. Each Livewire component's `mount()` reads/forgets `exercise_settings`, calls `LearningPathQuestionGenerator`, stores results in `session('exercise_practice_session')`.

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
| `interval-construction-practice` | `allowed_intervals`, `allowed_root_notes`, `octave` |
| `interval-comparison-practice` | `allowed_interval_pairs` (array of `['C,D','C,E']` pairs), `octave`, `clef` |
| `chord-practice` | `allowed_chord_types`, `allowed_root_notes`, `voicing`, `include_inversions`, `distractor_pool` |
| `scale-practice` | `allowed_scale_types`, `allowed_root_notes`, `direction`, `distractor_pool` |
| `rhythm-practice` | `time_signatures`, `allowed_note_values`, `tempo_range`, `bars` |
| `melodic-dictation` | `note_pool`, `melody_length`, `clef`, `key_signatures`, `tempo_range`, `bars` |
| `single-note-practice` | `allowed_notes`, `octave_range`, `distractor_count` |

Interval name abbreviations (`m2`, `M2`, `TT`, `8ve`, etc.) from exercise-setup are **not** understood by the generator — they must be mapped to full names (`Minor 2nd`, `Major 2nd`, `Tritone`, `Perfect Octave`) in the Livewire component before passing to the generator.

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

15 locales in `lang/`. `SetLocale` middleware applies `app()->setLocale()` from `User->locale`. `LearningPathExercise` has a `translations` JSON column; use `$exercise->getLocalizedTitle()`. Language switched via `POST /language/switch`.
