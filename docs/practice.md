# Practice module

Judge0-backed coding exercises attached to content atoms (`kind: exercise`).

## Flow

1. Content seeds (or AI generation) attach an `exercise` atom with `meta.language`, `meta.starter_code`, and `meta.tests[]`.
2. `GET /api/v1/practice/nodes/{slug}/exercise` returns the exercise (without expected outputs).
3. `POST /api/v1/practice/nodes/{slug}/attempts` runs all tests via Judge0, stores an `attempts` row, and on `accepted` bumps `user_skills.mastery` (+15, cap 100).
4. Failed submissions may receive LLM-derived `error_tags` when Ollama is available.

## Configuration

`config/judge0.php`:

- `JUDGE0_HOST` — default `http://judge0-server:2358` in Sail
- `JUDGE0_TIMEOUT` — seconds (default 30)
- `language_ids` — map track language → Judge0 language id

## Exercise atom meta

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
