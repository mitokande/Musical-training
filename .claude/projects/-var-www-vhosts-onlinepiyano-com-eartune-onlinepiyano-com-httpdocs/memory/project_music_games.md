---
name: project-music-games
description: Music Games section added to EarTune — 4 game types, leaderboard, free/premium limits
metadata:
  type: project
---

Music Games section (`/games`) implemented as a new feature area within the EarTune app.

**Why:** User wanted a gamified ear training section to complement the structured practice pages.

**How to apply:** When working on games, look in `app/Livewire/Games/`, `app/Http/Controllers/GameController.php`, `app/Models/GameScore.php`, and `resources/views/games/`.

**Architecture decisions:**
- Games live at `/games` (hub) and `/games/{slug}` (individual game)
- Scores stored in `game_scores` table; `GameScore` model handles leaderboards
- Free/premium limits via `config/plans.php` keys `games_daily_plays` and `games_leaderboard`
- All game logic (timers, state machines) in Alpine.js within Livewire `@script` blocks
- `EarTuneAudio` (Tone.js Sampler) defined inline in `games/show.blade.php`
- Nav: games added to `partials/navbar.blade.php` `$navItems` array (key: `'games'`)
- i18n key: `app.nav.games` in all `resources/lang/*/app.php` files

**4 game types:**
1. `note-rush` — 60s sprint, identify notes, streak multiplier
2. `melody-memory` — Simon Says with piano keyboard UI
3. `interval-blitz` — 10s/question, 3 lives, 12 intervals, difficulty selector
4. `chord-clash` — pick correct chord from 2 options, difficulty tiers (major/minor → 7ths → inversions)
