# 0007. Слои Repository + Service / Repository and Service layers

- **Status:** Accepted
- **Date:** 2026-06-24

---

## Русский

### Контекст

Контроллеры должны оставаться тонкими. Бизнес-логика не должна жить в Eloquent-моделях и HTTP-слое. Нужен единый стиль для всех DDD-модулей.

### Решение

В каждом модуле:

| Слой | Ответственность |
|------|-----------------|
| **Controller** | Form Request, вызов сервиса, HTTP-ответ |
| **Service** | бизнес-правила, оркестрация, транзакции |
| **Repository** | доступ к данным (Eloquent, HTTP-клиенты внешних API) |
| **DTO** | вход/выход сервисов без привязки к HTTP |

Базовый контракт: `App\Modules\Shared\Contracts\RepositoryInterface`.

Внешние интеграции (Ollama, Judge0) оформляются как **Driver** + **Router** (Strategy), не как Repository.

Правила:

- Контроллер не вызывает `Model::query()` напрямую
- Сервис не знает о `Request` / `Response`
- Репозиторий не содержит бизнес-правил
- Межмодульные вызовы — через публичные сервисы, не через чужие репозитории

### Последствия

**Плюсы:** тестируемость, предсказуемые границы, место для новых паттернов (Action, Specification, State).

**Минусы:** больше файлов на простые CRUD; дисциплина на разработчике.

---

## English

### Context

Controllers must stay thin. Business logic must not live in Eloquent models or HTTP layer.

### Decision

Per module: Controller → Service → Repository, with DTOs at service boundaries. External APIs use Driver + Router (Strategy). Cross-module access via public services only.

### Consequences

**Pros:** testability, clear boundaries.

**Cons:** more boilerplate for trivial CRUD.
