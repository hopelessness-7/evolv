# Evolv — Модель данных / Data model (MVP)

MySQL 8 — основные данные. Qdrant — векторы атомов и RAG-документация. Meilisearch — полнотекстовые индексы (производные от MySQL).

**Languages:** [Русский](#русский) · [English](#english)

---

## ERD

```mermaid
erDiagram
    users {
        bigint id PK
        varchar email UK
        varchar password
        varchar name
        timestamp email_verified_at
        timestamps timestamps
    }

    user_profiles {
        bigint user_id PK_FK
        string timezone
        smallint daily_minutes
        json enabled_pillars
        json facets
        timestamp core_completed_at
        timestamp onboarding_completed_at
        timestamps timestamps
    }

    onboarding_questionnaires {
        bigint id PK
        string code
        string version
        string pillar
        string tier
        string title
        json schema
        json prompt_templates
        boolean is_current
        timestamp published_at
        timestamps timestamps
    }

    onboarding_sessions {
        bigint id PK
        bigint user_id FK
        bigint questionnaire_id FK
        string questionnaire_code
        string questionnaire_version
        string status
        json answers
        json interpreted
        json composed_prompts
        timestamp completed_at
        timestamps timestamps
    }

    onboarding_analytics_events {
        bigint id PK
        bigint user_id FK
        bigint session_id FK
        string event
        json payload
        timestamps timestamps
    }

    coach_daily_plans {
        bigint id PK
        bigint user_id FK
        date plan_date
        string mode
        string source
        json plan
        timestamps timestamps
    }

    user_notifications {
        bigint id PK
        bigint user_id FK
        string type
        string title
        text body
        json data
        timestamp read_at
        timestamp emailed_at
        timestamps timestamps
    }

    notification_preferences {
        bigint user_id PK_FK
        boolean email_enabled
        timestamps timestamps
    }

    knowledge_nodes {
        bigint id PK
        varchar slug UK
        varchar track
        varchar title
        text summary
        varchar status
        timestamps timestamps
    }

    knowledge_edges {
        bigint from_node_id PK_FK
        bigint to_node_id PK_FK
        varchar kind PK
    }

    content_versions {
        bigint id PK
        bigint node_id FK
        int version_no
        bigint parent_version_id FK
        varchar status
        bigint created_by_user_id FK
        timestamps timestamps
    }

    content_atoms {
        bigint id PK
        bigint version_id FK
        varchar kind
        text body_md
        json meta
        int order_in_version
        varchar qdrant_point_id
        timestamps timestamps
    }

    learning_plans {
        bigint id PK
        bigint user_id FK
        varchar track
        varchar status
        timestamp activated_at
        timestamps timestamps
    }

    learning_plan_steps {
        bigint id PK
        bigint plan_id FK
        bigint node_id FK
        int order_in_plan
        varchar status
        timestamp completed_at
    }

    user_skills {
        bigint id PK
        bigint user_id FK
        bigint node_id FK
        smallint mastery
        timestamp last_practiced_at
        timestamps timestamps
    }

    attempts {
        bigint id PK
        bigint user_id FK
        bigint node_id FK
        varchar kind
        json payload
        varchar verdict
        json error_tags
        int duration_ms
        json judge0_response
        timestamp created_at
    }

    srs_cards {
        bigint id PK
        bigint user_id FK
        bigint node_id FK
        timestamp due_at
        decimal ease
        int interval_days
        int repetitions
        timestamps timestamps
    }

    ai_generation_jobs {
        bigint id PK
        varchar kind
        json input
        varchar status
        bigint result_version_id FK
        text error
        timestamps timestamps
    }

    users ||--o| user_profiles : has
    users ||--o{ onboarding_sessions : has
    users ||--o{ onboarding_analytics_events : generates
    users ||--o{ coach_daily_plans : receives
    users ||--o{ user_notifications : receives
    users ||--o| notification_preferences : has
    onboarding_questionnaires ||--o{ onboarding_sessions : defines
    onboarding_sessions ||--o{ onboarding_analytics_events : logs
    users ||--o{ learning_plans : "owns"
    learning_plans ||--o{ learning_plan_steps : "contains"
    knowledge_nodes ||--o{ learning_plan_steps : "referenced by"
    knowledge_nodes ||--o{ knowledge_edges : "from"
    knowledge_nodes ||--o{ knowledge_edges : "to"
    knowledge_nodes ||--o{ content_versions : "has versions"
    content_versions ||--o{ content_atoms : "contains"
    content_versions }o--o| content_versions : "parent"
    users ||--o{ user_skills : "tracks"
    knowledge_nodes ||--o{ user_skills : "mastered in"
    users ||--o{ attempts : "submits"
    knowledge_nodes ||--o{ attempts : "attempted in"
    users ||--o{ srs_cards : "owns"
    knowledge_nodes ||--o{ srs_cards : "scheduled for"
    content_versions ||--o{ ai_generation_jobs : "produced by"
```

Роли и права хранит `spatie/laravel-permission` в своих стандартных таблицах (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`).

---

<a id="русский"></a>

## Русский

### Группы таблиц

| Группа | Таблицы | Модуль-владелец |
|--------|---------|-----------------|
| Идентификация | `users`, `personal_access_tokens`, `roles`, `permissions`, ... | Auth |
| Онбординг | `user_profiles`, `onboarding_questionnaires`, `onboarding_sessions`, `onboarding_analytics_events` | Onboarding |
| Coach | `coach_daily_plans` | Coach |
| Уведомления | `user_notifications`, `notification_preferences` | Notifications |
| Граф знаний | `knowledge_nodes`, `knowledge_edges` | Curriculum |
| Контент (канон + версии) | `content_versions`, `content_atoms` | Content |
| Маршрут обучения | `learning_plans`, `learning_plan_steps` | LearningPath |
| Прогресс и практика | `user_skills`, `attempts`, `srs_cards` | LearningPath / Practice |
| AI-задачи | `ai_generation_jobs` | AI |

### Онбординг (реализовано)

- `user_profiles` — агрегированный профиль: timezone, daily_minutes, enabled_pillars, facets по опросникам
- `onboarding_questionnaires` — версионированные опросники (seed JSON): `core`, `craft_lite`, `mind_lite`, `presence_lite`, `mind_*` extended
- `onboarding_sessions` — сессии с partial save (`answers`), интерпретацией (`interpreted`) и промптами (`composed_prompts`)
- `onboarding_analytics_events` — события воронки (status viewed, session started, …)

`tier`: `core` | `lite` | `extended`. См. [onboarding.md](onboarding.md).

### Coach (реализовано)

`coach_daily_plans` — кэш плана на день: `mode` (simplified/personalized), `source` (llm/fallback), JSON `plan`. UNIQUE `(user_id, plan_date)`. См. [coach.md](coach.md).

### Граф знаний

`knowledge_edges.kind`:

- `REQUIRES` — для изучения `to_node` нужно сначала пройти `from_node`
- `RELATED_TO` — связь без жёсткой зависимости
- `IS_NEW_VERSION_OF` — новая редакция узла, не меняя `slug`

Поиск зависимостей — recursive CTE по `knowledge_edges`. На объёмах MVP (сотни узлов на трек) — мгновенно.

### Канон и версии

Один `node_id` имеет несколько `content_versions`, среди которых не более одной с `status = 'active'` (партишал unique индекс). Канон = active-версия. Озеро версий хранит drafts, archived, и AI-сгенерированные drafts до промоции.

`content_atoms` принадлежат конкретной версии (`version_id`), не узлу напрямую. Это упрощает откат и сравнение версий. Поле `qdrant_point_id` ссылается на точку в коллекции Qdrant — векторы хранятся там, не в MySQL.

### Маршрут пользователя

`learning_plans` — текущий активный план для одного трека. Шаги (`learning_plan_steps`) — упорядоченный список узлов из графа, статусы (`locked`/`available`/`in_progress`/`completed`) считаются динамически по `user_skills` и зависимостям графа, но кэшируются в `learning_plan_steps.status` для быстрого UI.

Пользователь может иметь несколько планов (учу Node параллельно с SQL), но активный одновременно — один на трек.

### Практика и навыки

`attempts.error_tags` — JSON-массив строковых тегов (`confuses_var_let`, `mutates_state`, ...). На MVP — простой массив; позже можно эволюционировать в нормализованную таблицу.

`user_skills.mastery` — 0–100, обновляется по результатам attempts (формула — отдельная стратегия в `LearningPath`).

`srs_cards` — SM-2: `due_at`, `ease`, `interval_days`, `repetitions`.

### AI-задачи

`ai_generation_jobs` — outbox для async LLM-генерации:

- `kind` — `lesson_generation`, `quiz_generation`, `error_analysis`, ...
- `input` — JSON с параметрами
- `status` — `queued` / `running` / `succeeded` / `failed`
- `result_version_id` — на новую `content_versions`, если генерация создала контент

Очередь Laravel дёргает соответствующий job-класс, тот вызывает `LlmDriver` через модуль `AI`.

### Индексы (MVP)

| Таблица | Индекс |
|---------|--------|
| `knowledge_nodes` | UNIQUE `slug` |
| `knowledge_edges` | INDEX `(from_node_id, kind)` |
| `content_versions` | UNIQUE `(node_id)` WHERE `status='active'` (партишал) |
| `content_atoms` | INDEX `(version_id, order_in_version)` |
| `learning_plans` | UNIQUE `(user_id, track)` WHERE `status='active'` |
| `learning_plan_steps` | INDEX `(plan_id, order_in_plan)` |
| `user_skills` | UNIQUE `(user_id, node_id)` |
| `srs_cards` | INDEX `(user_id, due_at)` |
| `attempts` | INDEX `(user_id, created_at)` |

### Что вне MySQL

| Данные | Где живут |
|--------|-----------|
| Векторы атомов контента | Qdrant, коллекция `content_atoms`, dim=768 |
| Векторы RAG-документации | Qdrant, коллекция `docs` |
| Поисковые индексы | Meilisearch, индексы `nodes`, `content` |
| Сессии, broadcast, кэш | Redis |
| Файлы (аватары, аттачи) | MinIO (dev) / S3 (prod) |

### Объёмы на MVP (ориентир)

| Таблица | Порядок строк |
|---------|---------------|
| `users` | 10² |
| `knowledge_nodes` | 10² на трек × 1–3 трека |
| `knowledge_edges` | ~2–3× от nodes |
| `content_versions` | ~1.5× от nodes |
| `content_atoms` | 10³–10⁴ |
| `learning_plan_steps` | users × ~50 |
| `attempts` | 10⁵+ при активной практике |
| `srs_cards` | users × active nodes |

---

<a id="english"></a>

## English

### Table groups

| Group | Tables | Owner module |
|-------|--------|--------------|
| Identity | `users`, `personal_access_tokens`, permission tables | Auth |
| Onboarding | `user_profiles`, `onboarding_questionnaires`, `onboarding_sessions`, `onboarding_analytics_events` | Onboarding |
| Coach | `coach_daily_plans` | Coach |
| Knowledge graph | `knowledge_nodes`, `knowledge_edges` | Curriculum |
| Content canon + versions | `content_versions`, `content_atoms` | Content |
| Learning route | `learning_plans`, `learning_plan_steps` | LearningPath |
| Progress and practice | `user_skills`, `attempts`, `srs_cards` | LearningPath / Practice |
| AI workflows | `ai_generation_jobs` | AI |

### Knowledge graph

`knowledge_edges.kind`: `REQUIRES`, `RELATED_TO`, `IS_NEW_VERSION_OF`. Traversal via MySQL recursive CTE; sufficient for MVP scale.

### Canon and versions

One active `content_version` per `node_id` (partial unique). Atoms belong to a specific version. `qdrant_point_id` references the embedding point in Qdrant.

### User route

`learning_plans` — one active plan per track per user; `learning_plan_steps` cache statuses for UI but are recomputable from `user_skills` and graph edges.

### Practice and skills

`attempts.error_tags` is a JSON array of string tags. `user_skills.mastery` (0–100) updates from attempts. `srs_cards` implements SM-2.

### AI jobs

`ai_generation_jobs` is the outbox for async LLM-driven content generation, processed by Laravel queue workers calling `LlmDriver`.

### Indexes, external stores, and volumes

Same as the Russian section.
