# Evolv — Архитектура / Architecture

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Видение

Evolv — самообучающаяся ИИ-платформа для освоения навыков, в первую очередь программирования. Это не чат-бот и не статичный курс: один общий граф знаний, по которому система прокладывает персональные маршруты, а ИИ постепенно достраивает и уточняет контент по результатам реальной практики пользователей.

### Топология

```
┌──────────────────────────────────────────────────────────────┐
│  Клиенты                                                      │
│  Quasar (web / PWA / Electron / Capacitor)                    │
│  Python desktop (после MVP)                                   │
└──────────────────────────────────────────────────────────────┘
                            │ HTTPS, Sanctum tokens
                            ▼
┌──────────────────────────────────────────────────────────────┐
│  Laravel 13 монолит (Sail)                                    │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ app/Modules/                                            │  │
│  │   Identity   Onboarding   Curriculum   Content          │  │
│  │   LearningPath   Practice   AI   Admin (Filament)       │  │
│  └────────────────────────────────────────────────────────┘  │
│   queue workers · scheduler · Reverb (WebSocket)              │
└──────────────────────────────────────────────────────────────┘
        │            │             │             │
        ▼            ▼             ▼             ▼
   ┌────────┐   ┌────────┐   ┌──────────┐   ┌────────┐
   │ MySQL  │   │ Redis  │   │  Qdrant  │   │ Meili- │
   │  8     │   │  7     │   │          │   │ search │
   └────────┘   └────────┘   └──────────┘   └────────┘
        │                                          ▲
        ▼                                          │
   ┌────────┐                                ┌─────────┐
   │ Judge0 │                                │ Ollama  │
   └────────┘                                └─────────┘
```

### Стек

| Слой | Инструмент | Назначение |
|------|------------|------------|
| Web framework | Laravel 13 + Sail | Бэкенд-монолит |
| Frontend | Vue 3 + Quasar | Web + PWA + Electron + Capacitor (отдельный репо) |
| Auth | Laravel Sanctum + spatie/laravel-permission | Токены + RBAC |
| Реляционная БД | MySQL 8 | Основные данные, граф (recursive CTE) |
| Векторы | Qdrant | Эмбеддинги контента, RAG-документация |
| Полнотекст | Meilisearch + Laravel Scout | Поиск по контенту/курсам |
| Кэш, очереди, broadcasting | Redis 7 | Стандарт Laravel |
| Real-time | Laravel Reverb | WebSocket-сервер |
| LLM | Ollama (MVP) через `LlmDriver` | Эмбеддинги + чат |
| Выполнение кода | Judge0 | Sandboxed code runners |
| Email (dev) | Mailpit | SMTP-перехват |
| Файлы (dev) | MinIO | S3-совместимое хранилище |
| Админ-панель | Filament 3 | CRUD + дашборды |
| Отладка (dev) | Laravel Telescope | Inspector запросов |
| Мониторинг (prod) | Laravel Pulse | Application performance |

См. также: [adr/0002-data-stores.md](adr/0002-data-stores.md).

### Доменные модули

| Модуль | Ответственность |
|--------|-----------------|
| **Identity** | Регистрация, вход, профиль, роли, Sanctum-токены |
| **Onboarding** | Захват цели, уровня, бюджета времени и стиля обучения |
| **Curriculum** | Граф знаний (узлы, рёбра REQUIRES / RELATED_TO / IS_NEW_VERSION_OF), `GraphRepository` |
| **Content** | Канон, версии, атомы (теория / сниппет / квиз), индексирование в Qdrant и Meilisearch |
| **LearningPath** | Персональный маршрут поверх общего графа, прогресс, рекомендации следующего шага |
| **Practice** | Задачи практики, отправка в Judge0, разбор результата, attempts, error_tags |
| **AI** | `LlmDriver`, генерация уроков, RAG-пайплайн, обогащение контента из агрегированных ошибок |
| **Admin** | Filament-панель: модерация, дашборды, конфиг |

Граница между модулями — публичные сервисы (контракты). Прямой доступ к моделям чужого модуля запрещён.

### Потоки данных

**Регистрация и онбординг:**
Client → Sanctum (`Identity`) → `Onboarding` сохраняет цели → выпуск личного маршрута (`LearningPath`).

**Открытие урока:**
Client → `LearningPath` (текущий шаг) → `Content` отдаёт активную версию атомов узла → ответ.

**Практика:**
Client отправляет код → `Practice` → Judge0 → результат + AI-разбор ошибок (`AI`) → запись `attempts` → обновление `user_skills` и `srs_cards`.

**AI-обогащение контента (асинхронно):**
Cron / queue → `AI` агрегирует `attempts.error_tags` → запрашивает LLM сгенерировать дополнительные атомы → новая `content_version` (статус `draft`) → ручная или авто-промоция в `active`.

**Поиск:**
Запись/обновление атома → job в Meilisearch и эмбеддинг → Qdrant.

### Окружение

Локально всё поднимается одной командой Sail. Состав compose: laravel-app, mysql, redis, qdrant, meilisearch, judge0, ollama, mailpit, minio, reverb (отдельный процесс), queue worker(ы).

### Что вне MVP

Учительские курсы, группы, домашка, нотификации, расписание сессий, портфолио, git-экспорт, биллинг, мобильные сборки, Python-десктоп, OPS-стек (Grafana/Prometheus/Loki), Neo4j.

### Связанные документы

- [data-model.md](data-model.md) — таблицы и связи
- [adr/](adr/) — обоснования решений

---

<a id="english"></a>

## English

### Vision

Evolv is a self-evolving AI-powered skill learning platform, primarily for software development. It is neither a chatbot nor a static course catalog: one shared knowledge graph, with personalised routes per learner, refined and extended by AI from aggregated practice data.

### Topology

See the diagram in the Russian section — identical.

### Stack

| Layer | Tool | Purpose |
|-------|------|---------|
| Web framework | Laravel 13 + Sail | Backend monolith |
| Frontend | Vue 3 + Quasar | Web/PWA/Electron/Capacitor (separate repo) |
| Auth | Sanctum + spatie/laravel-permission | Tokens + RBAC |
| Relational DB | MySQL 8 | Domain data, graph via recursive CTE |
| Vector | Qdrant | Content embeddings, RAG |
| Full-text | Meilisearch + Scout | Content/course search |
| Cache, queues, broadcast | Redis 7 | Laravel default |
| Real-time | Laravel Reverb | WebSocket |
| LLM | Ollama (MVP) via `LlmDriver` | Embeddings + chat |
| Code execution | Judge0 | Sandboxed runners |
| Email (dev) | Mailpit | SMTP capture |
| Files (dev) | MinIO | S3-compatible |
| Admin panel | Filament 3 | CRUD + dashboards |
| Debug (dev) | Laravel Telescope | Request inspector |
| Monitoring (prod) | Laravel Pulse | App performance |

### Domain modules

`Identity`, `Onboarding`, `Curriculum`, `Content`, `LearningPath`, `Practice`, `AI`, `Admin`. Cross-module access only via public services.

### Data flows

Registration/onboarding, lesson opening, practice submission, async AI content enrichment, and search indexing — same as Russian section.

### Environment

Single Sail compose: laravel-app, mysql, redis, qdrant, meilisearch, judge0, ollama, mailpit, minio, reverb, queue workers.

### Out of MVP scope

Teacher courses, groups, assignments, notifications, scheduling, portfolio, git export, billing, mobile builds, Python desktop, OPS stack (Grafana/Prometheus/Loki), Neo4j.

### Related documents

- [data-model.md](data-model.md)
- [adr/](adr/)
