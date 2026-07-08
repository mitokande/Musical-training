## Core Practice Engine, Learning Path & Games

### Practice types

10 practice types share one route (`GET /practice/{slug}`) and one dispatch blade (`resources/views/practice.blade.php`, `@switch($slug)`), each backed by a dedicated `App\Livewire\Practice*` component + `resources/views/livewire/practice-*.blade.php`. Models are thin Eloquent records (`SingleNotePractice`, `MelodicIntervalPractice`, `HarmonicIntervalPractice`, `IntervalDirectionPractice`, `IntervalComparisonPractice`, `IntervalConstructionPractice`, `ChordPractice`, `ScalePractice`, `RhythmPractice`, `MelodicDictationPractice`) — almost no business logic lives on the models themselves; it's all in `LearningPathQuestionGenerator`, `TonalMelodyGenerator`, `MusicTheoryService`, and the Livewire `mount()` methods (see `HandlesPracticeData` trait, documented in CLAUDE.md).

`PracticeAiMelodicDictation` (`app/Livewire/PracticeAiMelodicDictation.php`) literally `extends PracticeMelodicDictation`, overriding `mount()` to auto-derive key/tempo/note-pool from a `difficulty` string instead of explicit settings. Its blade (`practice-ai-melodic-dictation.blade.php`) is a hand-maintained "re-skinned clone" of `practice-melodic-dictation.blade.php` — two files that must be kept in sync by hand, a real duplication risk (already flagged in CLAUDE.md, confirmed by reading both).

### LearningPathQuestionGenerator (`app/Services/LearningPathQuestionGenerator.php`, 1074 lines)

Single `generate(LearningPathExercise, int): Collection` entry point, `match`-dispatched over `config_json['practice_type']` to 10 private generator methods. Depends on `MusicTheoryService`, `TonalMelodyGenerator`, `RhythmDistractorService`. Notable internals:

- `selectDistractors()` — a compatibility shim: if the caller's config has neither `distractor_count` nor `distractor_mode`, it falls back to legacy per-type heuristics (closure param `$fallback`); only the AI-difficulty flow supplies both keys, enabling a `'near'` (closest-by-semitone, hardest) vs `'far'/'mixed'` (random) distractor strategy. Three code paths for "pick wrong answers" (legacy fallback, near, mixed) is real complexity surface.
- `canonicalIntervalPool()` builds all 12 semitone-distance interval names for use as a distractor source.
- `octavesWithinClefRange()` filters candidate octaves so generated notes/intervals stay drawable on the selected clef — a clef-range constraint that must be kept in sync with `MusicTheoryService::CLEF_RANGES` (renders elsewhere, e.g. `partials/responsive-notation.blade.php`).
- Session serialization (`serializeForSession`/`reconstructFromSession`) round-trips unsaved Eloquent models through the session as arrays — fragile by construction (CLAUDE.md already documents that `_options` relations don't survive `getAttributes()` and must be manually re-added by callers).

### TonalMelodyGenerator (`app/Services/TonalMelodyGenerator.php`, 958 lines)

Canonical source of all melodic-dictation melodies (both LP and Exercise-Setup flows funnel through it — confirmed no inline melody generation elsewhere). Enforces per-difficulty tonal rules (beginner/intermediate/advanced): diatonic pool, tonic-triad start, level-based cadence, ≥70% step/third motion, leap-size/count caps, no consecutive leaps, contrary-motion leap resolution, range caps (M6/octave/M10). `generateMelody()` retries a weighted random walk against `validateMelody()`, with a deterministic stepwise fallback if generation keeps failing — a reasonable "guaranteed to terminate" design, but the retry-until-valid pattern means worst-case generation cost is unbounded in theory (bounded in practice by the fallback). Covered by `tests/Unit/TonalMelodyGeneratorTest.php`.

### Three entry-point flows, one gap in the fourth

Per CLAUDE.md: Direct nav, Learning Path, Exercise Setup Studio, and AI Assisted Exercises all converge on `/practice/{slug}`. Reading `AIController::generatePractices()` (`app/Http/Controllers/AIController.php:79`) closely:

- Melodic-dictation-only selection is special-cased to redirect to `/practice-ai/melodic-dictation` (own page/component, see above).
- Everything else funnels through `$localTypeSlugs` → `LearningPathQuestionGenerator`, deterministically, **not via OpenAI** — a comment at line 112-114 says explicitly: *"Types handled via OpenAI structured output (none currently — single-note migrated to deterministic local generation so user note/clef settings apply)."* `$aiPracticeClasses = []`. So despite the "AI Assisted Exercises" branding and the OpenAI plumbing still present in the controller (schema-building, `OpenAI::client()`, quota-error handling around line 267-334), **no practice questions are currently LLM-generated** — only `generateCoachNotes()` and `generateIntervalDirectionPractice()` (a separate, seemingly legacy/unused endpoint at line 23) still call OpenAI. This is either intentional (cost/determinism win) or dead-weight code — worth confirming with the team which.

### Console commands: data-quality tooling

- `exercises:validate-questions` (`ValidateQuestions.php`, 86 lines) and `exercises:repair-questions` (`RepairQuestions.php`, 184 lines) both operate over the 5 interval-family practice types (melodic, harmonic, direction, construction, comparison) and exist specifically because **seeded questions can have `direction`/`answer` fields that mismatch the actual note data** — i.e., past data-generation bugs left bad rows in production, and these commands are the cleanup/audit tooling (repair has a `--dry-run`). This implies the interval question data has needed manual correction before; worth checking `RepairQuestions` is still run after any generator change.
- `piano:cache-notes` (`CachePianoNotes.php`, 184 lines) downloads all piano note audio samples (4 octaves) from an external API for offline/faster playback — a build/deploy-time asset step, not part of request handling.

### Games

Two parallel, disconnected implementations exist for the same 4 games (chord-clash, interval-blitz, melody-memory, note-rush):

1. **Live path**: `GameController::show()` (`app/Http/Controllers/GameController.php`) renders `games/show.blade.php`, which does `@include('partials.games.' . $slug, ...)` — i.e. the actual game logic lives in `resources/views/partials/games/*.blade.php` as large, mostly-vanilla-JS/canvas blade files (plus `note-catcher` and `note-fall`, which have **no** Livewire equivalent at all).
2. **Dead path**: `app/Livewire/Games/{ChordClash,IntervalBlitz,MelodyMemory,NoteRush}.php` each render `resources/views/livewire/games/*.blade.php`. **Grepping the whole codebase for `livewire:games` or the `Livewire\Games\*` class names finds zero references anywhere outside `app/Livewire/Games/` itself.** These 4 Livewire components + their 4 blades are unused/orphaned code and safe to delete (or were an earlier abandoned implementation before the team moved game logic into plain JS partials).

The current **uncommitted working tree** shows a large rewrite of exactly the live-path game partials: `chord-clash.blade.php` (+661/-…), `interval-blitz.blade.php` (+472), `melody-memory.blade.php` (+819), `note-catcher.blade.php` (+671), `note-fall.blade.php` (+1092/net restructure), `note-rush.blade.php` (+439) — over 3,100 insertions / 1,000 deletions total across these 6 files. This is consistent with an active in-progress UI/gameplay overhaul of the games system (variant test pages `games/test-c.blade.php` and `GameController::testA/testB/testC` suggest active A/B iteration on the games index page too). Scoring/limits/leaderboards (`GameScore` model, plan-gated daily play limits, guest session-based tracking capped at 1 play/type and 3 total) are stable and unaffected by the blade rewrite. Notably, a new personal best on any game now posts to the social feed via `FeedItem::recordGameHighScore()` — a cross-domain hook into the new Social feature (see Social domain section).

### Risks & Observations

- **Dead code**: `app/Livewire/Games/*` (4 components) and `resources/views/livewire/games/*` (4 blades) appear fully orphaned — confirm and remove.
- **Duplication**: `practice-ai-melodic-dictation.blade.php` is a manually-synced clone of `practice-melodic-dictation.blade.php`; any JS/structure fix to one must be mirrored in the other by hand, with no test enforcing parity.
- **Misleading naming**: "AI Assisted Exercises" generates all but coach-notes content deterministically/locally today; OpenAI plumbing for practice-question generation is present but currently unreachable (`$aiPracticeClasses = []`). If this is intentional, the naming/UI copy may still promise LLM-authored questions it doesn't deliver.
- **Historical data-integrity issue**: existence of `exercises:validate-questions` / `exercises:repair-questions` implies interval practice data has previously drifted from correctness (direction/answer mismatches) — a recurring risk any time the generator or seed data changes; run `validate-questions` after seed/generator edits.
- **Session-based state fragility**: multiple flows (LP, Exercise Setup, AI) push unsaved Eloquent-like data through PHP session arrays with manual re-hydration (`serializeForSession`/`reconstructFromSession`, `buildModelFromData`) — correctness depends on every caller remembering to re-attach computed fields (e.g. `options`, `_options`) that don't survive `getAttributes()`. This is a recurring source of subtle bugs per CLAUDE.md's own callouts.
- **In-flight games rewrite**: the scale of the uncommitted games-partial diff (3,100+ insertions) means this domain is mid-refactor; a thorough manual review/playtest of note-fall, note-catcher, chord-clash, interval-blitz, melody-memory and note-rush is warranted before merging, since none of this is covered by automated tests (no test files reference the games JS/gameplay logic).
