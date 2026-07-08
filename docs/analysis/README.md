# Harmoniva Codebase Analysis

*Generated 2026-07-07. Laravel 12 / PHP 8.2, Livewire v3. Snapshot includes the current uncommitted working tree, which is substantial — see below.*

This is a domain-by-domain analysis of the codebase, split into one file per domain:

1. [Core Practice Engine, Learning Path & Games](01-practice-engine.md)
2. [Admin Panel & Operations](02-admin.md)
3. [Email Center & Support Inbox](03-email-center.md)
4. [Teacher Marketplace / Teacher CRM](04-teacher-crm.md)
5. [Social Feed & Following, AI Coach / AI Chat](05-social-ai.md)
6. [Auth, Users, Roles, Plans & Billing](06-auth-billing.md)
7. [Infrastructure & Cross-Cutting Concerns](07-infrastructure.md)

Each file ends with its own "Risks & Observations" section.

---

## Executive Summary

**Repo state.** `main` is 5 commits ahead of a much older baseline, but the *working tree* at the time of this analysis carried **283 changed/untracked files** (154 modified, ~129 new), +16,149/-10,676 lines against HEAD. This is not incidental drift — it's a nearly-complete, unmerged **Teacher Marketplace/CRM** feature (the largest single addition: ~27 models, 15 controllers, 9 services, ~90 tests, 7 migrations), plus a fully-built but also-uncommitted **Email Center** (Amazon SES campaigns/automations/support inbox), a new **Social Feed & Following** feature (partially committed at `82ed3cd`), and an in-progress rewrite of the **Games** UI. None of the Teacher CRM or Email Center code is in git history yet.

**Top cross-cutting risks, ranked:**

1. **Uncommitted work at risk of loss.** The entire Teacher CRM (arguably the product's biggest bet right now) and Email Center exist only in the working tree. A `git clean`, disk failure, or bad `git reset --hard` would destroy months of work. → **Commit this in logically-scoped chunks as soon as possible.**
2. **Build tooling isn't tracked by git at all.** `package.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` were deleted from the git index in commit `b7db79b` and never restored (content itself is fine/unchanged). A fresh clone of `main` cannot run `npm run build` today. Trivial fix, high blast radius if unnoticed.
3. **A real secrets file is sitting untracked and un-ignored.** `.env.backup-20260705-121149` (55 lines, live credentials) is not covered by `.gitignore` (which only excludes the literal name `.env.backup`, not timestamped variants). One `git add -A` away from leaking secrets into history.
4. **No payment gateway exists anywhere in the app.** Not a Teacher-CRM-specific gap — there is no Stripe/Paddle/PSP integration at all. `Subscription`/`Invoice`/`Coupon` and the new `TeacherPaymentLink`/subscription-benefit engine are all manually admin-managed or explicitly documented as "Phase 1, no billing." If self-serve payment is expected soon, this is a from-scratch build, not a config change.
5. **Test coverage is heavily skewed.** The core music-theory engine is well-tested; the ~70-route Teacher CRM booking/review/student-messaging surface and most of the Email Center webhook/campaign pipeline have thin-to-zero Feature test coverage.
6. **i18n is quietly broken in several places.** Hardcoded Turkish strings bypass the app's 15-locale `__()` system in `AiChatController`, `AiCoachController`, `CheckUserRestriction`, `CheckPlanFeature`, and `SocialAuthController`. Separately, the new `teacher.php` lang file only exists for 7 of 15 locales.
7. **Authorization is almost entirely role-string/middleware-based**, not per-resource. Only one Laravel Policy exists in the whole codebase (`TeacherProfilePolicy`, scoped to the new Teacher CRM). Admin actions like impersonation, feed-post deletion, and CRM-note editing have no ownership checks or audit trail beyond what `spatie/activitylog` happens to catch.
8. **Three independent messaging subsystems** (generic `Message` table for admin support + social DMs, `TeacherConversation*` for teacher↔student, `SupportConversation*` for IMAP support inbox) share no code or abstraction.
9. **Some AI-usage admin dashboards are structurally dead**: `AiCoachingSession` is read by four admin analytics views but never written by either AI controller that would populate it — those widgets always show zero.
10. **Single point of failure on the scheduler**: no dedicated queue worker; email sending, campaign processing, support-mail ingestion, and the new teacher-benefit-expiry job all depend on an external crontab entry (`php8.2 artisan schedule:run`) that nothing in the repo can verify or alert on.

**What's in good shape, worth calling out explicitly:** the Email Center's SNS/SES webhook handling is a genuinely well-engineered idempotent pipeline (real signature verification, monotonic status transitions, dedup hashing) with unusually thorough tests; the new `Follow`/`FeedItem` social data model is clean, idiomatic Eloquent; the Teacher CRM's assignment engine correctly reuses the existing `LearningPathQuestionGenerator` rather than inventing a parallel one; and the AI-generated exercise-question path is sanity-checked through `MusicTheoryService` before reaching users.
