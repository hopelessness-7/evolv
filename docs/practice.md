# Practice module

Judge0-backed coding exercises attached to content atoms (`kind: exercise`).

## Flow

1. Content seeds (or AI generation) attach an `exercise` atom with `meta.language`, `meta.starter_code`, and `meta.tests[]`.
2. `GET /api/v1/practice/nodes/{slug}/exercise` returns the exercise (without expected outputs).
3. `POST /api/v1/practice/nodes/{slug}/attempts` runs code via Judge0 **without** Judge0-side expected_output, then compares stdout locally with normalized trailing whitespace/newlines. Stores `attempts`; on `accepted` bumps `user_skills.mastery` (+15, cap 100).
4. Failed submissions may receive LLM-derived `error_tags` when Ollama is available.

## Configuration

`config/judge0.php`:

- `JUDGE0_HOST` — default `http://judge0-server:2358` in Sail
- `JUDGE0_TIMEOUT` — seconds (default 30)
- `language_ids` — map track language → Judge0 language id

## Exercise atom

Task text lives in `body_md` (returned as `prompt`). Optional coaching text:

```json
{
  "language": "php",
  "starter_code": "<?php\n",
  "hints": ["First nudge", "Second nudge"],
  "tests": [
    {
      "label": "greeting",
      "stdin": "",
      "expected_output": "Hello, Evolv!"
    }
  ]
}
```

Also accepted: single `hint` or `trace_hint` string in meta.

## Local fallback

If Judge0 is unreachable or returns Internal Error (common on Docker Desktop / WSL isolate), PHP exercises fall back to `LocalPhpDriver` (`php` CLI in the app container).

Set `JUDGE0_DRIVER=auto|judge0|local` in `.env` (default `auto`).

## GET exercise response

```json
{
  "atom_id": 6,
  "node_id": 1,
  "node_slug": "laravel.n1",
  "title": "Eloquent N+1",
  "summary": "…",
  "criterion": "…",
  "prompt": "Markdown task statement from body_md",
  "hints": ["…"],
  "language": "php",
  "starter_code": "<?php\n",
  "tests": [{ "label": "…", "stdin": "" }]
}
```

## Exercise atom meta (Judge0)

```json
{
  "language": "php",
  "starter_code": "<?php\n",
  "tests": [
    {
      "label": "greeting",
      "stdin": "",
      "expected_output": "Hello, Evolv!"
    }
  ]
}
```

## API

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/practice/nodes/{slug}/exercise` | Exercise for node |
| POST | `/api/v1/practice/nodes/{slug}/attempts` | Submit code `{ code, atom_id }` |

Bearer token required (Sanctum).
