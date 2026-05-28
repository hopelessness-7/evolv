# 0001. Монолит с DDD-модулями / Monolith with DDD modules

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

Evolv реализует несколько связанных доменов: identity, граф знаний, контент, обучающий маршрут, практика, AI-генерация. Проект ведёт один разработчик, ранний MVP.

Варианты:

1. Микросервисы (gateway + core + LLM-worker + practice-runner)
2. Монолит без модульных границ
3. Монолит с явными DDD-модулями

### Решение

**Монолит на Laravel 13 + Sail с DDD-модулями** в `app/Modules/<Domain>`.

Каждый модуль содержит:

- `Models/` — Eloquent
- `Services/` — доменные сервисы
- `Http/Controllers/` — HTTP-эндпоинты
- `Database/Migrations/` — миграции модуля
- `Routes/` — `routes/api.php` модуля
- `Tests/` — Pest

Межмодульное взаимодействие — только через публичные сервисы модуля, не через модели напрямую.

### Последствия

**Плюсы:** один деплой, одна БД, один отладчик; чёткие границы доменов; модуль можно вынести в отдельный сервис, когда нагрузка потребует, без переписывания доменной логики.

**Минусы:** ответственность за дисциплину границ — на разработчике; межмодульные циклы возможны, нужно ловить ревью.

**Альтернатива:** микросервисы дают физические границы, но дают и сетевой overhead, deploy-сложность и репликацию доменных контрактов. Для одного разработчика — антипаттерн.

---

## English

### Context

Evolv covers multiple related domains (identity, knowledge graph, content, learning path, practice, AI generation), maintained by a single developer at MVP stage.

Options: microservices, flat monolith, modular monolith with DDD.

### Decision

**Laravel 13 monolith + Sail with DDD modules** under `app/Modules/<Domain>`. Each module owns its models, services, controllers, migrations, routes, and tests. Cross-module access only via public services.

### Consequences

**Pros:** single deploy and database; clear domain boundaries; modules can be extracted as services later when justified by load.

**Cons:** boundary discipline is enforced manually; cyclic dependencies possible without code review attention.
