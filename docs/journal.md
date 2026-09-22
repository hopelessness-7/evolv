# Journal module (v1)

Thin notebook for learner notes and daily reflections. Linked from Coach plan `tools` with `type: notebook`.

## Table

`journal_entries`: `user_id`, `kind` (`reflection`|`note`|`question`), `body`, optional `node_slug`, optional `plan_date`.

## API

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/journal/entries` | List (filters: `node_slug`, `plan_date`; pagination: `page`, `per_page`) |
| POST | `/api/v1/journal/entries` | Create |
| PATCH | `/api/v1/journal/entries/{id}` | Update owned entry |
| DELETE | `/api/v1/journal/entries/{id}` | Delete owned entry |

Bearer token required (Sanctum).
