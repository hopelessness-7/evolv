# 0004. Аутентификация на gateway / Authentication on gateway

- **Status:** Accepted
- **Date:** 2026-05-23

---

## Русский

### Контекст

Нужны регистрация, вход и JWT. Основной стек бизнеса — Laravel (core), но gateway сознательно на **NestJS** (прокси + auth).

Варианты: auth в Laravel (Sanctum); auth в gateway; внешний IdP (Auth0).

### Решение

**Auth в gateway (NestJS):**

- Таблица `auth.users`, миграции gateway
- JWT access (15 мин) + refresh (7 дней)
- Публичные маршруты `/api/auth/*`; остальное `/api/*` — проверка Bearer
- Прокси в core с заголовками: `X-User-Id`, `X-User-Email`, `X-Internal-Token`

**Core (Laravel):**

- Нет паролей и регистрации
- Middleware `InternalAuth` — без валидного internal token запрос отклоняется
- Идентификация пользователя — `X-User-Id`

### Последствия

**Плюсы:** одна публичная точка входа; core не торчит наружу; разделение identity и обучения.

**Минусы:** нет FK из `public.*` на `auth.users` на MVP (можно добавить позже).

**Безопасность:** длинный случайный `INTERNAL_SERVICE_TOKEN`; core только во внутренней Docker-сети.

---

## English

### Context

Registration, login, JWT. Business logic in Laravel; gateway on NestJS by choice.

### Decision

**Auth on gateway:** owns `auth.users`, issues JWT, proxies with `X-User-Id` and `X-Internal-Token`. **Core** has no passwords; trusts internal token and user header.

### Consequences

**Pros:** single public edge; clear identity vs learning domain split.

**Cons:** no cross-schema FK on MVP.

**Security:** strong internal token; core on internal network only.
