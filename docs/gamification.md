# Gamification module (v1)

Thin XP / streak / puzzle-unlock layer. Source modules emit domain events; Gamification listens and updates a per-user profile.

## Behavior

| Source | Event | XP |
|--------|-------|----|
| Learning path step completed | `StepCompleted` | +10 (`step_complete`) |
| Correct quiz answer | `QuizAnswered` (`correct: true`) | +5 (`quiz_correct`) |
| Practice attempt accepted | `AttemptAccepted` | +20 (`practice_accepted`) |

Amounts live in `config/gamification.php` (`gamification.xp.*`).

**Streak:** On each award, if `last_active_on` is yesterday → `current_streak++`; if already today → no streak change; otherwise → reset to `1`. `longest_streak` tracks the max. `last_active_on` becomes today when the streak changes.

**Puzzle pieces:** `StepCompleted` may carry `puzzlePieces` from node `meta.puzzle_pieces`. Unique piece ids are merged into `unlocked_pieces`. Catalog keys are listed in `config/gamification.pieces` for UI reference.

**Idempotency (v1):** None strict. Quiz may award every correct check; practice awards on every accept.

## Persistence

- `gamification_profiles` — one row per user (`user_id` PK): xp, streaks, `last_active_on`, `unlocked_pieces`
- `xp_ledger` — append-only awards (`source`, `amount`, `meta`, `created_at` only)

## API

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/gamification/me` | Profile (zeros for new users) |

Bearer token required (Sanctum).

```json
{
  "xp": 0,
  "current_streak": 0,
  "longest_streak": 0,
  "last_active_on": null,
  "unlocked_pieces": []
}
```

## Learn BFF

`GET /api/v1/learn/today` includes optional `gamification` (same shape as `/me`).
