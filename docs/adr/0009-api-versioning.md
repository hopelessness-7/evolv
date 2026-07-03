# 0009. Версионирование API / API versioning

- **Status:** Accepted
- **Date:** 2026-06-24

---

## Русский

### Контекст

Клиенты (Quasar, mobile) будут жить долго; контракт API будет меняться. Нужно правило, позволяющее вводить breaking changes без поломки старых клиентов.

### Решение

Все модульные HTTP-роуты регистрируются под префиксом **`/api/{version}`**.

- Текущая версия: **`v1`**
- Файлы роутов модуля: `app/Modules/<Domain>/Routes/v1.php`
- Имена роутов: префикс `v1.` (например `v1.auth.login`)
- Конфиг: `config/api.php` (`supported_versions`, `default_version`)

Правила:

1. **Breaking change** → новый файл `v2.php`, старый `v1.php` остаётся до deprecation
2. **Non-breaking** (новое поле в ответе, новый optional query param) → можно в той же версии
3. Глобальные unversioned эндпоинты не используем (кроме Laravel `/up` health)
4. Модуль **Auth**: URL-сегмент `auth` (`/api/v1/auth/...`), код в `app/Modules/Auth/`.

### Последствия

**Плюсы:** явный контракт; параллельные версии; проще для мобильных клиентов.

**Минусы:** дублирование роутов при миграции v1→v2; дисциплина на ревью.

---

## English

### Decision

Module routes under `/api/{version}/`, starting with `v1`. Route files: `Routes/v1.php`. Breaking changes ship as new version files.

### Consequences

**Pros:** stable client contracts.

**Cons:** version maintenance overhead.
