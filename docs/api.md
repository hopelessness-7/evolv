# Evolv API (v1)

Base URL: `/api/v1`

Interactive docs: `/docs/api` (Scramble) when enabled.

## Auth

| Method | Path | Auth |
|--------|------|------|
| POST | `/auth/register` | — |
| POST | `/auth/login` | — |
| POST | `/auth/logout` | Bearer |
| GET | `/auth/me` | Bearer |

## Onboarding

| Method | Path | Auth |
|--------|------|------|
| GET | `/onboarding/status` | Bearer |
| GET | `/onboarding/questionnaires` | Bearer |
| GET | `/onboarding/questionnaires/{code}` | Bearer |
| POST | `/onboarding/sessions` | Bearer |
| PATCH | `/onboarding/sessions/{id}` | Bearer |
| POST | `/onboarding/sessions/{id}/complete` | Bearer |
| POST | `/onboarding/core` | Bearer |

## Coach

| Method | Path | Auth |
|--------|------|------|
| GET | `/coach/daily-plan` | Bearer |

Query: `date` (optional), `refresh=1` to regenerate.

## Curriculum

| Method | Path | Auth |
|--------|------|------|
| GET | `/curriculum/nodes` | Bearer |
| GET | `/curriculum/nodes/{slug}` | Bearer |
| GET | `/curriculum/nodes/{slug}/prerequisites` | Bearer |
| GET | `/curriculum/nodes/{slug}/related` | Bearer |
| GET | `/curriculum/entry-nodes` | Bearer |

## Content

| Method | Path | Auth |
|--------|------|------|
| GET | `/content/nodes/{slug}` | Bearer |
| POST | `/content/nodes/{slug}/quiz-check` | Bearer |

Body for quiz-check: `{ "atom_id": 1, "answer": "B" }`.

## Learning path

| Method | Path | Auth |
|--------|------|------|
| GET | `/learning-path/tracks` | Bearer |
| GET | `/learning-path/plan` | Bearer |
| GET | `/learning-path/progress` | Bearer |
| GET | `/learning-path/current-step` | Bearer |
| GET | `/learning-path/current-step?with_content=1` | Bearer |
| POST | `/learning-path/steps/{id}/start` | Bearer |
| POST | `/learning-path/steps/{id}/complete` | Bearer |

## Learn (BFF)

| Method | Path | Auth |
|--------|------|------|
| GET | `/learn/today` | Bearer |
| GET | `/learn/current-lesson` | Bearer |

## Practice

| Method | Path | Auth |
|--------|------|------|
| GET | `/practice/nodes/{slug}/exercise` | Bearer |
| POST | `/practice/nodes/{slug}/attempts` | Bearer |

Body: `{ "code": "...", "atom_id": 1 }`.

## AI

| Method | Path | Auth |
|--------|------|------|
| GET | `/ai/ping` | Bearer |
| POST | `/ai/content/nodes/{slug}/generate` | Bearer |

Lesson generation is queued; requires `php artisan queue:work` and Ollama.

## Notifications

| Method | Path | Auth |
|--------|------|------|
| GET | `/notifications` | Bearer |
| GET | `/notifications/{id}` | Bearer |
| GET | `/notifications/preferences` | Bearer |
| PATCH | `/notifications/preferences` | Bearer |

## Health

| Method | Path | Auth |
|--------|------|------|
| GET | `/health/infra` | — |
