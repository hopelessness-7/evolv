# Evolv

Self-evolving AI-powered platform for learning code — a living textbook, not a fixed video sequence.

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Идея

Три столпа продукта:

| Столп | Описание |
|-------|----------|
| **Путь** | Один общий граф знаний с зависимостями. Персональные маршруты — это разные обходы общего графа, а не клоны курсов под каждого пользователя. |
| **Канон** | Одна актуальная версия темы; история редакций сохраняется как озеро версий. |
| **Coach** | План на день, подсказки, разбор ошибок, адаптивная сложность. |

ИИ генерирует и уточняет контент на основе агрегированных ошибок пользователей. С ростом пользователей граф становится точнее и плотнее.

### Архитектура

Монолит на Laravel 13 с DDD-модулями. Подробности:

- [docs/architecture.md](docs/architecture.md) — топология, стек, модули, потоки данных
- [docs/data-model.md](docs/data-model.md) — таблицы и связи (ERD)
- [docs/adr/](docs/adr/) — обоснования архитектурных решений

### Стек

- **Backend:** Laravel 13 + Sail (PHP 8.3)
- **Frontend:** Vue 3 + Quasar — отдельный репозиторий, сборки под web / PWA / desktop / mobile
- **Auth:** Laravel Sanctum + spatie/laravel-permission
- **Данные:** MySQL 8, Qdrant (векторы), Meilisearch (полнотекст), Redis 7
- **LLM:** Ollama локально, через абстракцию `LlmDriver` (см. [ADR-0006](docs/adr/0006-llm-driver-abstraction.md))
- **Выполнение кода:** Judge0
- **Real-time:** Laravel Reverb
- **Админ-панель:** Filament 3

### Требования к окружению

- Docker Desktop / Docker Engine + Compose v2
- Make (опционально, для шорткатов)
- Git

### Лицензия

MIT — см. [LICENSE](LICENSE).

---

<a id="english"></a>

## English

### Concept

| Pillar | Description |
|--------|-------------|
| **Path** | One shared knowledge graph; per-user routes are different traversals, not cloned courses. |
| **Canon** | One active version per topic; full revision history kept as a version lake. |
| **Coach** | Daily plan, hints, error analysis, adaptive difficulty. |

AI generates and refines content from aggregated user practice data. The graph improves as the user base grows.

### Architecture

Laravel 13 monolith with DDD modules. See:

- [docs/architecture.md](docs/architecture.md) — topology, stack, modules, data flows
- [docs/data-model.md](docs/data-model.md) — schema and ERD
- [docs/adr/](docs/adr/) — decision records

### Stack

- **Backend:** Laravel 13 + Sail (PHP 8.3)
- **Frontend:** Vue 3 + Quasar — separate repository; builds for web / PWA / desktop / mobile
- **Auth:** Laravel Sanctum + spatie/laravel-permission
- **Data stores:** MySQL 8, Qdrant (vectors), Meilisearch (full-text), Redis 7
- **LLM:** Ollama locally, behind the `LlmDriver` abstraction ([ADR-0006](docs/adr/0006-llm-driver-abstraction.md))
- **Code execution:** Judge0
- **Real-time:** Laravel Reverb
- **Admin UI:** Filament 3

### Requirements

- Docker Desktop / Docker Engine + Compose v2
- Make (optional)
- Git

### License

MIT — see [LICENSE](LICENSE).
