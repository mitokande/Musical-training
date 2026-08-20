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

### Account deletion (soft)

`User` uses `SoftDeletes`. Everything goes through `App\Services\Account\AccountDeletionService` — never call `$user->delete()` directly, or the side-effects below are skipped.

Deleting: cancels live subscriptions (immediate), archives the `TeacherProfile` (public listings live in their own table, so the soft-delete scope does not hide them), revokes Sanctum tokens, then **anonymises the unique identity columns** — `email` → `deleted-user-{id}@deleted.invalid`, `username` → `deleted_user_{id}`, `google_id` → null — keeping the originals in `deleted_email` / `deleted_username`. The anonymisation is what makes deletion feel permanent *and* lets the same person sign up again: `unique:users` validation ignores model scopes, so a retained address would otherwise block re-registration with "email already taken".

For the member it is a one-way door — the soft-delete scope hides them from the auth provider, so login, password reset and API tokens all fail. Self-service is `DELETE /profile` (`ProfileController::destroy`, the Settings tab), confirmed by password or, for Google-only accounts, by typing their own address. Admins see deleted accounts under the **Deleted** tab of `/admin/users` (`segment=deleted`, `onlyTrashed`), can `restore` (original e-mail handed back only if nobody claimed it meanwhile — the unique index does not care about `deleted_at`) or `forceDelete`. Admin routes for a trashed member need `->withTrashed()`.

Because the global scope makes `belongsTo(User::class)` resolve to null for deleted accounts, user-visible lists filter with `whereHas(...)` instead of rendering a null actor: social feed, game leaderboards, public teacher reviews, student↔teacher conversations, school rosters. Any new list joining users needs the same guard.

Self-service *suspension* used to occupy this slot in the profile Settings tab and was removed with this feature; `suspended_at` and `isSuspended()` remain (API auth still honours them), but nothing in the UI writes them any more.

Three entry points, one service:
- **Web, signed in** — `DELETE /profile` from the profile Settings tab.
- **Public page** — `GET /delete-account` (`pages.delete-account`, no auth middleware). Required by the Play Store / App Store data-deletion policies: reachable without the app or an account. Signed-in visitors get the same confirm-and-delete modal inline (it posts to `DELETE /profile`, which is why `ProfileController::destroy` redirects with `back()` rather than a fixed route); signed-out visitors get sign-in and support-e-mail routes. It is in `config('locales.public_pages')` + `page_sections` (`delete_account`), so it carries localized URLs, hreflang and sitemap entries like any other public page, and it is linked from the footer.
- **Mobile app** — `DELETE /api/v1/me/account` (`throttle:api-auth`), password or `confirm_email` for Google accounts, optional `reason`.

The page's data disclosure (what is deleted / what is retained and why) must stay true to what the service actually does — it is the statement the app stores review against.

Tests: `tests/Feature/AccountDeletionTest.php`, `tests/Feature/AccountDeletionPageTest.php`, plus the deletion cases in `tests/Feature/ProfileTest.php`.

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

**Read `docs/i18n-guide.md` before any translation or multilingual-SEO work.** It carries the rules that are not derivable from the code: how to actually verify a language is complete (five separate scans — missing keys, keys called but absent, hardcoded blade text, inline-JS strings, hardcoded controller flash/abort messages), the per-language address form, the "canonical value stays English, only the label is translated" rule for music terms, the meta-length limits (Romance languages run 20–25% longer than English and overflow Google's cutoff), the canonical/hreflang single source, and the repo-specific traps (never `git stash` here; root-owned files; quoting styles in lang files).

15 locales in `resources/lang/` (NOT the Laravel-11-default root `lang/` — new lang files must go under `resources/lang/{locale}/`); `en`, `de`, `es`, `fr`, `it`, `pt`, `tr` are fully translated. `SetLocale` middleware applies `app()->setLocale()` from `User->locale` — note `users.locale` has a DB default of `'tr'`, so any code path creating a user must set it explicitly. `LearningPathExercise` has a `translations` JSON column; use `$exercise->getLocalizedTitle()`. Language switched via `POST /language/switch`.

Music-theory vocabulary is localized through `music_label($canonical, 'chord'|'scale')` (`app.music.*`, 7 locales) while `data-answer`, Alpine values and `config_json` keep the canonical English names the generator expects. Inline scripts get the same map via `livewire/partials/music-labels.blade.php`.

Canonical / hreflang / `<html lang>` / `og:locale` all come from `App\Services\Seo\PublicPageSeo`, shared by `ShareSeoContext`. A `/{locale}` URL is only advertised as a real translation once its `pages.*` section actually exists in that locale (`locale_page_translated()`), which is what keeps untranslated variants out of the index.

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

### Analytics & error tracking (PostHog)

PostHog **EU Cloud** (`https://eu.i.posthog.com`), configured entirely from `config/posthog.php`. **An empty `POSTHOG_KEY` disables everything** — the snippet is not rendered and every server call is a no-op — which is how local dev and CI stay silent. `POSTHOG_ENABLED=false` is the kill switch that keeps the key in place.

Browser side (`resources/js/posthog.js`, a Vite entry — `posthog-js` is version-pinned in `package.json`, not loaded from a CDN):
- Config is server-rendered into `window.__posthogSettings` by `resources/views/partials/posthog.blade.php`, which is included **next to `@include('partials.google-analytics')` in all 49 views that have it**. New full-page views must include both.
- Autocapture, `capture_exceptions` (JS error tracking), and session replay with `maskAllInputs` — on-screen text is recorded and readable; only what the user *types* is masked.
- `person_profiles: 'identified_only'`. Authenticated requests call `posthog.identify()`; a page with no authenticated user calls `posthog.reset()` **only** when a stale identity is present (`$is_identified`), so ordinary anonymous views keep their device id.

Server side (`App\Services\Analytics\PostHogService`, singleton):
- Every method is best-effort — failures are logged, never thrown back into the caller. The `lib_curl` consumer sends with a 1 ms curl timeout (fire-and-forget), so captures add no request latency. Tests force `POSTHOG_CONSUMER=noop`.
- `distinctId()` prefers the user id, then falls back to the `distinct_id` in posthog-js's own `ph_{key}_posthog` cookie so one visitor does not become two people across the SDKs.
- Person properties are limited to `role`/`plan`/`locale`/`country`/`signed_up_at` — deliberately no name or email. `personProperties()` and the Blade partial must stay in sync.

What is captured server-side (so ad-blockers cannot drop the top of the funnel):
- `user_registered` (with `method`: password vs google), `user_logged_in`, `email_verified` — via `App\Listeners\RecordAuthAnalytics`, wired with `Event::listen` in `AppServiceProvider::boot()`.
- `subscription_activated` / `subscription_renewed` / `subscription_cancelled` — via `SubscriptionService::track()`. Most of these originate from a Stripe webhook where no browser exists.
- Unhandled exceptions — the `$exceptions->report()` callback in `bootstrap/app.php`. Laravel only runs reportable callbacks for exceptions passing `shouldReport()`, so 404s and validation failures never reach error tracking.

Tests: `tests/Feature/PostHogTest.php`.
