# 0003. Аутентификация через Sanctum / Authentication via Sanctum

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

Клиенты Evolv — Quasar-приложение (web + PWA + desktop через Electron + mobile через Capacitor) и потенциальный Python-клиент. Все клиенты ходят в один Laravel-монолит. Нужен API-токен, который понимают SPA, mobile и native-приложения.

Варианты: Sanctum, Passport (OAuth2), Fortify, внешний IdP (Auth0/Clerk).

### Решение

**Laravel Sanctum** для всех клиентов.

- SPA Quasar — cookie-based stateful auth (через `sanctum/csrf-cookie`)
- Mobile (Capacitor) и desktop (Electron) — API-токены с явным scope
- Python-клиент — API-токены

`spatie/laravel-permission` управляет ролями и правами поверх Sanctum: на MVP — `user` и `admin`; позже `teacher`, `student`.

### Последствия

**Плюсы:** один пакет, два режима под все клиенты; нет внешних зависимостей; абсолютно идиоматично Laravel.

**Минусы:** Sanctum не делает OAuth2; если потребуется выпуск токенов сторонним приложениям («login with Evolv»), переходим на Passport — рефакторинг будет ограничен auth-модулем.

---

## English

### Context

Clients are Quasar (web/PWA/desktop via Electron/mobile via Capacitor) plus a potential Python client. All hit one Laravel monolith. Needs a token mechanism that works for SPA, mobile, and native clients.

### Decision

**Laravel Sanctum** — cookie-stateful for SPA, API tokens for mobile/desktop/Python. Roles and permissions via `spatie/laravel-permission`.

### Consequences

**Pros:** single package, idiomatic Laravel, covers all client types.

**Cons:** no OAuth2; if needed later, migrate the auth module to Passport.
