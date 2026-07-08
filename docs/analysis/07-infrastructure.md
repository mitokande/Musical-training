## Infrastructure & Cross-Cutting Concerns

### Routing (`routes/web.php`, 570 lines, +314/-68 uncommitted vs HEAD)

The file has roughly doubled in size in the working tree. Groups, in order of appearance:

- **Public marketing pages** (~30 routes): closures returning `pages.*` views — pricing, solutions (students/teachers/schools), resources (help/FAQ/blog/guides), company, legal. No controllers, no auth.
- **Core practice app** (`/dashboard`, `/learn`, `/progress`, `/ai-exercises`, `/piano-studio`, `/practice/{slug}`, exercise-setup, practice-mixed, practice-ai, games) — matches CLAUDE.md's documented architecture.
- **Dev/debug routes**: `/dev/interval-stats` (marked TEMPORARY in a comment), `/dev/midi/*` (admin-gated MIDI upload/inspection), `/games/a`, `/games/b`, `/games/c` (explicitly commented "Temporary design test pages — remove after picking a variant").
- **Profile** (`auth` group): edit, avatar, suspend, extended music profile, questionnaire.
- **NEW: Social** — `/feed`, `/messages`, `/u/{username}` public profile (`auth+verified`).
- **NEW: Teacher public surface** — `/teachers/{slug}` show + booking page, `/sitemap.xml`.
- **NEW: Teacher self-service onboarding** — `POST /teacher/become` (any authed user can open a "Basic" teacher account).
- **NEW: Teacher CRM** (`prefix('teacher')`, `middleware(['auth','teacher'])`, ~70 routes) — dashboard, profile editor, services, videos, media, students (notes/tags/rewards), relationships, invitations, classes, assignments (incl. AI-suggest), messaging, calendar/availability/appointments, payment links. This is by far the largest single addition.
- **NEW: Student-facing teacher-ecosystem routes** (`auth` group, no `teacher` role) — `/my-teachers`, `/teacher-invitations/{token}` accept flow, `/teacher-messages/*`, `/my-appointments/*`, `/teachers/{slug}/slots|book|reviews`, `/assignments` (student view of `StudentAssignmentController`).
- **School routes** — largely unchanged (profile edit/logo).
- **Admin** (`prefix('admin')`, ~170 routes) — existing practice/user/content/payments/CRM-notes/AI-coach-admin sections plus **NEW**: Community feed moderation, Teacher Profile moderation (approve/reject/suspend/reinstate/force-private/tier/recalculate-benefits), Teacher Review moderation, System Health, and a full **Email Center** admin sub-app (dashboard, logs, suppressions, settings, test-send, templates w/ live preview, campaigns w/ segment-count/send/cancel/test-send, automations).
- **Legacy redirect**: `Route::redirect('articles', '/admin/content')` — confirms `ArticleApprovalController` (deleted in this diff) was folded into `ContentController`.
- **NEW: Public webhook & tracking endpoints** — `POST /webhooks/aws/ses/events` (SNS), `/email/open/{token}`, `/email/click/{token}` (signed), `/email/unsubscribe/{token}` GET+POST (signed, RFC 8058).

Note: `/games/{slug}` is a wildcard route declared *after* the three literal `/games/a|b|c` test routes but *before* nothing conflicts since Laravel matches literals first — still, those three dev routes have no `->name()` and aren't gated by `auth`/`admin`, so they're publicly reachable in production right now if not removed.

### Scheduler (`routes/console.php`)

Confirmed still accurate per CLAUDE.md — no dedicated queue worker; everything rides `schedule:run`:
- `queue:work --stop-when-empty --max-time=55 --tries=3` every minute, `withoutOverlapping()` — drains the DB queue in ~55s bursts.
- `email:process-campaigns` every minute.
- `email:run-automations` every 15 minutes.
- `support:fetch-mail` every 5 minutes.
- **NEW**: `TeacherSubscriptionBenefitService::expireLapsedBenefits()` daily, via `Schedule::call()` (not a dedicated Artisan command) — expires lapsed teacher premium incentive benefits.

All of this is inert unless the host's user crontab runs `php8.2 artisan schedule:run` every minute — nothing in the repo enforces or documents that crontab entry beyond the CLAUDE.md prose.

### Testing

`phpunit.xml`: two suites (Unit, Feature), SQLite in-memory, source coverage over `app/`. Deliberately forces `APP_CONFIG_CACHE`/`APP_ROUTES_CACHE`/`APP_EVENTS_CACHE` to nonexistent paths so a stale production cache never leaks into tests (good defensive touch).

- `tests/Unit` (9 files): almost entirely music-theory-engine correctness (`TonalMelodyGeneratorTest`, `RhythmAllaBreveTest`, `RhythmDistractorTest`, `RhythmGroupingTest`, `SingleNoteOctaveMappingTest`, `MusicTheoryServiceTest`), plus `EnvironmentSafetyTest` and `RestInjectionTest`.
- `tests/Feature` (21 files): Auth (5), core social (`FeedTest`, `FollowTest`, `MessagingTest`, `PublicProfileTest`), `ProfileTest`, `DevMidiViewerTest`, interval-consistency tests, `EmailCenterTest`, and 6 Teacher-CRM tests (`TeacherAccountTest`, `TeacherBenefitTest`, `TeacherCrmPackage2Test`, `TeacherCrmPackage3Test`, `TeacherProfileEditorTest`, `TeacherProfileModerationTest`).

**Coverage gap**: the Teacher CRM route surface is ~70 routes wide, but only `TeacherCrmPackage2Test` (23 assertions touching students/classes/assignments) and `TeacherCrmPackage3Test` (49 assertions touching messages/calendar) exercise it — there is **no test file at all** for booking/appointments from the *student* side (`MyAppointmentsController`, `TeacherBookingController::slots/book`), `TeacherReviewController`, `TeacherInvitationAcceptController`, `StudentTeacherMessageController`, or `StudentAssignmentController`. The Email Center's SES webhook handling, campaign sending pipeline, and support-inbox IMAP fetch also have only the one `EmailCenterTest` covering them (worth confirming it covers `SesWebhookController`/`SupportInboxController` and not just dispatch).

### i18n (`resources/lang/`)

15 locales exist: `ar, de, en, es, fr, it, ja, ko, nl, pl, pt, ru, sv, tr, zh`.

The new `teacher.php` lang file (git status: untracked) exists for only **7 of 15**: `de, en, es, fr, it, pt, tr`. Missing entirely for: **`ar, ja, ko, nl, pl, ru, sv, zh`** (8 locales). Any user on those 8 locales hitting a teacher-CRM or public teacher-profile page will fall back to Laravel's missing-translation-key behavior (raw key string) unless a fallback locale is configured — worth checking `config/app.php`'s `fallback_locale`.

### Database

67 migration files total. Rough growth by year-month prefix:
- `0001_01` (Laravel framework defaults): 3
- `2025_12`: 2
- `2026_01`: 6
- `2026_05`: **45** — the bulk of the schema, likely when practice/learning-path/exercise-setup/plans/CRM-notes machinery was built.
- `2026_06`: 4
- `2026_07`: 7 — all **untracked**, and all the newest feature work: `add_is_restricted_to_users_table`, `create_email_center_tables`, `create_support_inbox_tables`, `extend_teacher_profiles_for_teacher_crm`, `create_teacher_crm_tables`, `create_notifications_table`, `create_teacher_crm_package2_tables`, `create_teacher_crm_package3_tables`.

New seeders (all untracked): `PlansSeeder`, `SystemSettingsSeeder`, `EmailCenterSeeder` (per CLAUDE.md, seeds the 6 standard automations disabled).

### Config files (`config/*.php`)

Standard Laravel set (`app`, `auth`, `cache`, `database`, `filesystems`, `logging`, `mail`, `queue`, `session`, `services`) plus app-specific:
- `config/plans.php` — the free/premium feature-limit matrix referenced by `User::getPlanLimit()` per CLAUDE.md.
- `config/countries.php` — static ISO country-name list, presumably for profile/school forms.
- `config/excel.php` — `maatwebsite/excel` export config (chunk size etc.) — backs the admin CSV/XLSX exports (`users.export`, `reports.export`).
- `config/email-center.php` (untracked) — SES/SNS configuration-set documentation, referenced by the Email Center subsystem.

Nothing unusual beyond the two SES/Excel additions being logically scoped to their own files rather than crammed into `services.php`.

### `app/View/Components/`

Only 4 components: `AppLayout`, `GuestLayout` (standard Breeze layout wrappers), `notechart.php`, `practice_content.php` (music-notation-specific, presumably render the staff/notation partials referenced across practice blades).

### `app/Http/Controllers/Public/` (new, untracked)

Two controllers, both deliberately anonymous/no-auth-required:
- `SitemapController` — generates `/sitemap.xml` from `TeacherProfile::publiclyVisible()` only (explicit code comment: draft/rejected/suspended/hidden profiles must never appear).
- `TeacherPublicProfileController` — public teacher profile page; gates unapproved/hidden profiles to owner+admin only (`abort_unless($isOwner || $isAdmin, 404)`), and explicitly lifts a site-wide `NoIndex` middleware flag for approved profiles only (`$request->attributes->set('allow_indexing', true)`), implying the site defaults to `noindex` and opts in per-page — worth confirming that `NoIndex` middleware default in `bootstrap/app.php`.

### `app/Http/Controllers/Webhooks/` (new, untracked)

Single controller: `SesWebhookController::events()` — decodes raw JSON body (not Laravel's request-validated input, since SNS posts raw JSON), delegates to `SnsMessageValidator` (checks payload structure/signature — 403 on failure) then `SesEventProcessor`. Correctly CSRF-exempted via `bootstrap/app.php` `validateCsrfTokens(except: ['webhooks/aws/ses/*', 'email/unsubscribe/*'])` — the unsubscribe exemption is justified by an inline comment (RFC 8058 one-click POST from mail clients, which won't carry a CSRF token). No other routes are CSRF-exempted, so this is a narrowly scoped, intentional exception rather than a blanket bypass.

### Console Commands (`app/Console/Commands/`)

6 total: `CachePianoNotes`, `RepairQuestions`, `ValidateQuestions` (pre-existing, exercise-content maintenance) and 3 new: `FetchSupportMail`, `ProcessEmailCampaigns`, `RunEmailAutomations` (all Email Center, all untracked, all wired into `routes/console.php`).

### Build tooling — `package.json` / `vite.config.js` / `tailwind.config.js` / `postcss.config.js`

**These 4 files are on disk but git has no record of them at all.** `git log --all -- package.json` shows they were present in the "Initial commit" (`4778476`) and then **deleted from the git index** in commit `b7db79b` ("harmoniva") — `git show b7db79b --stat` shows all four as pure deletions (`package.json | 21 -`, etc.), with no corresponding re-add in any later commit. `git status` now reports them as untracked (`??`), meaning the working-tree copies are effectively local-only. Content is unchanged from what was last committed (Tailwind 3.1, `@tailwindcss/vite` 4.0, Vite 7, `laravel-vite-plugin` 2.0, Alpine 3.4) — this isn't a version bump gone wrong, it's an accidental `git rm --cached`-style removal that nobody has fixed since. **A fresh `git clone` + `npm install && npm run build` would fail today** because Vite has no config to find.

### `composer.json` highlights

Laravel `^12.0`, PHP `^8.2`. Notable packages beyond CLAUDE.md's list: `laravel/socialite ^5.27`, `maatwebsite/excel ^3.1` (admin exports), `webklex/php-imap ^6.2` (support-inbox IMAP fetch), `openai-php/client ^0.18.0`, `spatie/laravel-activitylog ^4.12`.

---

### Risks & Observations

1. **Build tooling is untracked in git (see above)** — `package.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` were deleted from the index in commit `b7db79b` and never restored. Anyone cloning `main` fresh cannot build frontend assets. This should be re-added (`git add package.json vite.config.js tailwind.config.js postcss.config.js`) as its own fix, independent of the current huge feature diff.

2. **`.env.backup-20260705-121149` sits untracked in the repo root** (55 lines, world-readable perms `-rwxr-xr-x`). It is **not** covered by `.gitignore` — the ignore file only excludes the exact name `.env.backup`, not the timestamped variant actually produced. A careless `git add -A` / `git add .` would commit real secrets to history. This should be deleted or moved outside the repo, and `.gitignore` should use a glob (`.env.backup*`) to prevent recurrence.

3. **Test coverage is heavily skewed toward the music-theory engine and light on the new Teacher CRM / booking / Email Center surfaces.** ~70 new teacher-CRM routes and the entire student-facing booking/messaging/review/assignment flow have partial-to-no Feature test coverage. Given CLAUDE.md's testing conventions (`composer test`), this is the area most likely to regress silently.

4. **i18n gap**: 8 of 15 locales (`ar, ja, ko, nl, pl, ru, sv, zh`) have no `teacher.php` translation file while the teacher-facing UI (dashboard, profile, booking, CRM) is entirely new and extensive — likely to surface raw translation keys for those users unless a fallback locale masks it.

5. **Three unguarded, unnamed dev/test routes** (`/games/a`, `/games/b`, `/games/c`) are live with no `auth`/`admin` middleware and an explicit "remove after picking a variant" comment — low risk but should be tracked as cleanup debt, alongside `/dev/interval-stats` (also marked TEMPORARY, though at least `auth`-gated).

6. **Scheduler is a single point of failure**: with no dedicated queue worker, all of Email Center's campaign sending, automation emails, support-mail ingestion, and now the daily teacher-benefit-expiry job depend entirely on an external crontab entry the repo cannot verify or enforce. If that crontab entry is ever removed/misconfigured, mail, support ingestion, and benefit expiry all silently stop with no in-app alerting evident from the code reviewed (the new System Health admin page — `SystemHealthController` — may address this; out of scope for this fork to confirm).
