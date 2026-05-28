# Evolv — Модель данных / Data model (MVP)

Postgres 16, расширения: `vector`, `pg_trgm`.

Схемы: `auth` (gateway), `public` (домен core). На bootstrap — **раздельные схемы** для явного владения данными.

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Схема `auth` (gateway)

#### `auth.users`

| Поле | Тип | Описание |
|------|-----|----------|
| id | uuid PK | `gen_random_uuid()` |
| email | varchar(255) UNIQUE | логин |
| password_hash | varchar(255) | bcrypt |
| name | varchar(255) nullable | отображаемое имя |
| created_at | timestamptz | |
| updated_at | timestamptz | |

---

### Схема `public` (core)

#### `knowledge_nodes`

Атом знания в графе (не «курс»).

| Поле | Тип | Описание |
|------|-----|----------|
| id | uuid PK | |
| slug | varchar(128) UNIQUE | напр. `node-js-closures` |
| track | varchar(64) | напр. `node-backend` |
| title | varchar(255) | заголовок |
| summary | text nullable | краткое описание |
| status | varchar(32) | `draft`, `published`, `archived` |
| created_at, updated_at | timestamptz | |

#### `knowledge_edges`

| Поле | Тип | Описание |
|------|-----|----------|
| from_node_id, to_node_id | uuid FK | |
| kind | varchar(32) | `REQUIRES` (нужно знать до), `RELATED_TO`, `IS_NEW_VERSION_OF` |

#### `content_versions`

Запись в озере версий для узла.

| Поле | Тип | Описание |
|------|-----|----------|
| node_id | uuid FK | |
| version_no | int | монотонно для узла |
| parent_version_id | uuid nullable | линия merge |
| status | varchar(32) | `draft`, `active`, `archived` |

Один `active` на `node_id` (partial unique).

#### `content_atoms`

Единица материала (теория, сниппет, квиз).

| Поле | Тип | Описание |
|------|-----|----------|
| kind | varchar(32) | `theory`, `snippet`, `quiz` |
| body_md | text | Markdown |
| meta | jsonb | опции квиза, язык сниппета |
| embedding | vector(768) | дедуп / похожесть |

#### `user_progress`

Состояние пользователя на узле: `locked`, `available`, `in_progress`, `completed`.

#### `user_skills`

Мастерство 0–100, `last_practiced_at` — вход для кривой забывания.

#### `attempts`

Попытка практики: `payload` (код/SQL), `verdict`, `error_tags` (вектор ошибки, напр. `confuses_var_let`).

#### `srs_cards`

Интервальное повторение (SM-2): `due_at`, `ease`, `interval_days`.

### Пример обхода графа

```sql
WITH RECURSIVE reachable AS (
  SELECT id FROM knowledge_nodes WHERE slug = 'node-js-basics'
  UNION
  SELECT e.to_node_id
  FROM knowledge_edges e
  JOIN reachable r ON e.from_node_id = r.id
  WHERE e.kind = 'REQUIRES'
)
SELECT * FROM knowledge_nodes WHERE id IN (SELECT id FROM reachable);
```

### Эмбеддинги

Модель `nomic-embed-text` (768), индекс ivfflat после наполнения данными.

### Сиды

[shared/seeds/node-track.json](../shared/seeds/node-track.json) — заглушка трека Node (полный граф — в фазе контента).

---

<a id="english"></a>

## English

### Schema `auth` (gateway)

#### `auth.users`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | `gen_random_uuid()` |
| email | varchar(255) UNIQUE | login identifier |
| password_hash | varchar(255) | bcrypt |
| name | varchar(255) nullable | display name |
| created_at | timestamptz | |
| updated_at | timestamptz | |

### Schema `public` (core)

Same tables as above. Key concepts:

- **knowledge_nodes** — atomic topic, not a course
- **knowledge_edges** — REQUIRES, RELATED_TO, IS_NEW_VERSION_OF
- **content_versions / content_atoms** — canon + version lake, optional `vector(768)`
- **user_progress / user_skills** — route and mastery
- **attempts** — practice with `error_tags` vector
- **srs_cards** — spaced repetition (SM-2)

Graph traversal SQL and embeddings — identical to the Russian section.

Seed stub: [shared/seeds/node-track.json](../shared/seeds/node-track.json).
