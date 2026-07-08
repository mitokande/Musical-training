## Social Feed & Following

**Introduced in:** commit `82ed3cd` "feat(social): implement social feed and messaging features with follow functionality". One cosmetic uncommitted tweak on top (`social-feed.blade.php` composer width `max-w-2xl` → `w-full`).

### Data model
- `Follow` (`app/Models/Follow.php`) — simple pivot row (`follower_id`, `followed_id`) backing `follows` table. Wired into `User` via proper `belongsToMany(User::class, 'follows', ...)` relations: `following()`, `followers()`, plus helpers `isFollowing()`, `follow()`, `unfollow()`, `followersCount()`, `followingCount()` (`app/Models/User.php:328-383`). Clean, idiomatic pivot — not an improvised join table.
- `FeedItem` (`app/Models/FeedItem.php`) — polymorphic-by-`type` activity log (`new_member`, `follow`, `post`, `achievement`), `metadata` JSON cast. Static factory methods (`recordPost`, `recordFollow`, `recordAchievement`, `recordGameHighScore`, `checkStreakAchievement`) are the single source of truth for writing feed events — good pattern, avoids scattered `FeedItem::create()` calls. `currentStreakForUser()` derives a consecutive-day streak from `DailyExerciseCount` rows (date-diff walk), used both for feed achievements and surfaced in the AI Coach/Chat context (see below).
- `FeedLike` — join table (`feed_item_id`, `user_id`) for likes.
- `Message` (`app/Models/Message.php`, table `messages`) — **shared/generic messaging table**, disambiguated by a `type` string column. `Admin\MessageController` uses `type = 'support_ticket'`; the new `Messenger` Livewire component uses `type = 'message'` for peer-to-peer DMs. Same table also has a `parent_id` self-referencing thread structure and a `priority` field (support-ticket-oriented), unused by the social DM path.

### Livewire components
- `SocialFeed` (`app/Livewire/SocialFeed.php`) — feed rendering with two scopes: global `feed` (all users) and `following` (only followed users + self). Composing a post (`post()`, 1000-char validated), like toggling (`toggleLike`), and self-delete of own posts (`deletePost`, correctly scoped by `user_id` + `type='post'`). Eager-loads `actor`/`subject` and `withCount('likes')`, then a single extra query for `likedIds` — no N+1 per row.
- `FollowButton` (`app/Livewire/FollowButton.php`) — thin wrapper around `User::follow()/unfollow()`, guards against self-follow.
- `Messenger` (`app/Livewire/Messenger.php`) — inbox + thread view against the shared `Message` model, `mount(?string $to)` resolves target by username or id, marks messages read on conversation open. Conversation list is built in PHP by grouping an unbounded `Message::where(...)->get()` (loads **all** messages for the user, both directions, no pagination/limit) — fine at current scale, a scalability risk once users accumulate large histories.

### Routes / views
- `GET /feed` → `PageController::feedView()` → `feed.blade.php` (wraps `<livewire:social-feed>`).
- `GET /messages` → `PageController::messagesView()` → `messages.blade.php` (wraps `<livewire:messenger>`), takes `?to=` query param.
- `public-profile.blade.php` via `PageController::publicProfile(string $username)` shows follower/following counts and practice stats.
- Admin moderation: `DELETE community/{feedItem}` → `Admin\CommunityController::destroy` (untracked file) — lets admins remove feed posts.

### Architectural observation: three parallel messaging systems
This codebase now has **three independent messaging subsystems** that don't share code:
1. `Message` model — generic table used for both admin **support tickets** (`Admin\MessageController`) and the new social **peer DMs** (`Messenger` Livewire), disambiguated only by a `type` string.
2. `TeacherConversation` / `TeacherConversationMessage` / `TeacherConversationAttachment` (untracked, part of the in-flight Teacher CRM work) — teacher↔student messaging, entirely separate models/tables.
3. `SupportConversation` / `SupportMessage` (untracked, Email Center support inbox) — IMAP-fetched `support@harmoniva.app` threads, per CLAUDE.md.

There is no shared "conversation" abstraction; each was clearly built independently for its own flow. Not necessarily wrong (different domains, different constraints — e.g. IMAP threading vs. in-app DMs), but worth a deliberate look before a fourth messaging feature gets added, and worth confirming product intent (should teacher↔student DMs and general social DMs be the same inbox to the student? Currently they are not).

### Risks & Observations
- **Overloaded `Message` type column**: mixing `support_ticket` and `message` types in one table with a `priority` field that's meaningless for the social case is a maintenance smell — a missing `where('type', ...)` filter anywhere could leak support tickets into a user's DM inbox or vice versa. Currently every query does filter correctly, but there's no model-level scoping/enum enforcing it.
- **Messenger unbounded query**: `Messenger::render()` loads every message for the user with `->get()` (no `take()`/pagination) to build the conversation list — a heavy user (thousands of messages) will load the full history into memory every page render.
- **No visible authorization check** in `SocialFeed::toggleLike()` that the target `$feedItemId` actually exists before creating a like row (would just fail silently/DB-constraint on a bad id) — low severity.
- Positive: `Follow` is implemented as a real `belongsToMany` pivot, not a bolt-on array/JSON column — this is the right call and makes the eager-loading/counts in `following` scope efficient.

---

## AI Coach / AI Chat

There are **three separate OpenAI-backed features**, easy to conflate by name:

1. **AI Assisted Exercises** — `AIController::generatePractices()` (`app/Http/Controllers/AIController.php`, 944 lines total). Per CLAUDE.md flow: `/ai-exercises` → generates practice **questions** (not chat) for selected practice types, either locally (`buildLocalConfig`/`buildAdaptiveConfig`) or via OpenAI structured-ish JSON for question types not covered locally, then sanitizes AI output through `MusicTheoryService` (`sanitizeAIQuestions()`) before use — a good defensive step against a hallucinated/malformed interval or note name reaching the practice UI. Also owns `generateIntervalDirectionPractice()` and `generateCoachNotes()` (used elsewhere, e.g. `ReportController::aiCoach` at routes/web.php:475). This is the **question generator**, unrelated to the conversational coach below except for sharing the OpenAI client and (unenforced) model/key config.

2. **AI Coach** — `AiCoachController` (`app/Http/Controllers/AiCoachController.php`). `GET /ai-coach` renders a dashboard (practice stats, streak, questionnaire responses); `POST /ai-coach/generate` calls OpenAI once to produce a **structured 7-day weekly practice plan** as JSON (system prompt enforces exact JSON shape, strips markdown fences, `json_decode`s and validates). Model/temperature/max_tokens pulled from `SystemSetting` (admin-configurable, defaults `gpt-4.1-mini`, 2000 tokens, temp 0.5).

3. **AI Chat** — `AiChatController` (`app/Http/Controllers/AiChatController.php`). `GET /ai-chat` renders a conversational UI; `POST /ai-chat/send` is a turn-based coach chatbot with **per-user daily quota** enforced via `Cache` (`ai_chat:{user}:{date}` key), free vs. premium tiers for both message count (`DAILY_LIMIT_FREE`/`PREMIUM`) and response length (`MAX_WORDS_FREE`/`PREMIUM`, `max_tokens` 320/600). Conversation history kept in `session()`, capped to last 20 turns. This is the only one of the three with real cost-control against abuse.

Admin side: `Admin\AiCoachAdminController` (`ai-coach-admin/*` routes) shows usage dashboards and lets admins tune `SystemSetting` values (`ai_model`, `ai_max_tokens`, `ai_temperature`) that `AiCoachController` (and only `AiCoachController`) reads.

### OpenAI client usage pattern
Every controller repeats the same pattern inline: `config('services.openai.key')` → 500 if missing → `OpenAI::client($apikey)` → `$client->chat()->create([...])` inside a `try/catch (\Exception $e)` that logs (`AIController`) or just swallows into a generic user-facing error (`AiChatController`, `AiCoachController`). There is no shared service/wrapper class for OpenAI calls — each controller constructs the client and prompt independently, so retry/backoff, model fallback, and error-message consistency all have to be maintained in three places.

### `AiCoachingSession` — model exists but is never written
`app/Models/AiCoachingSession.php` (`user_id`, `session_data`, `model_used`, `tokens_used`) is **read extensively** by admin analytics — `Admin\AdminController` (weekly AI session/token widgets), `Admin\ReportController` (session count + token-usage trend chart), `Admin\UserController` (per-user AI session count), `Admin\AiCoachAdminController` (index dashboard, per-user profile). Grepping the whole `app/` tree, **nothing ever calls `AiCoachingSession::create()`** — neither `AiCoachController::generate()` nor `AiChatController::send()` persists a session row despite both making real OpenAI calls with token cost. This means every admin AI-usage dashboard is currently structurally dead (always reporting zero sessions/tokens), even though the UI and queries for it are fully built. This looks like a real, fixable gap rather than intentional.

### Risks & Observations
- **No cost/rate limiting on `AiCoachController::generate()`** — unlike `AiChatController`, there is no daily cap, cache check, or premium gate visible before it fires a `max_tokens=2000` OpenAI call. A user (or script) hitting `POST /ai-coach/generate` repeatedly has no built-in throttle.
- **Error message leakage**: `AiCoachController::generate()` catch block returns `'error' => 'Bir hata oluştu: '.$e->getMessage()` directly to the JSON response — raw exception text (potentially including request/library internals) is exposed to the client.
- **Untranslated hardcoded Turkish strings** in both `AiChatController` and `AiCoachController` (`'OpenAI API anahtarı yapılandırılmamış.'`, `'Bir hata oluştu...'`, `'AI yanıtı işlenemedi...'`) — bypasses the app's `resources/lang/{locale}` i18n system entirely (CLAUDE.md documents 15 supported locales via `__()`), so non-Turkish, non-English users get a Turkish error message on failure paths. `AiChatController`'s daily-limit messages *are* properly localized via a hardcoded `SUPPORTED_LOCALES`/`LIMIT_MESSAGES` array instead of `__()`, which is a second, different i18n mechanism living alongside the "real" one.
- **Prompt-injection surface**: `AiCoachController::buildContext()` and `AiChatController::buildUserContext()` interpolate user-controlled profile/survey data directly into the prompt sent to OpenAI; the system prompt tries to constrain output format/topic but there's no output-side validation on the chat path (only the weekly-plan path validates via `json_decode`). Low real-world severity (advice chatbot, not executing actions) but worth noting if AI output is ever used for anything beyond display.
- Positive: `AIController::sanitizeAIQuestions()` routing AI-generated exercise content back through `MusicTheoryService` before it reaches the practice UI is a solid defensive pattern the other two features don't have (they don't need it — they only produce prose/plan JSON, not gradeable content).
