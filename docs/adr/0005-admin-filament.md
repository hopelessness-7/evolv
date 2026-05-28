# 0005. Админ-панель на Filament / Admin panel on Filament

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

Платформе нужна административная панель: CRUD по контенту и пользователям, модерация, дашборд состояния системы, кастомные виджеты (очереди, статистика AI-генераций).

Варианты:

1. Отдельный сервис мониторинга/админки (свой фронт + бэк)
2. **Filament 3** — пакет Laravel для админ-панелей
3. Сторонние SaaS (Forest Admin, Retool)

### Решение

**Filament 3** внутри основного монолита. Регистрируется как отдельный модуль `app/Modules/Admin`. Доступ — по роли `admin` через `spatie/laravel-permission`.

- CRUD-ресурсы автогенерятся под Eloquent-модели
- Кастомные страницы — для очередей, состояния AI, статистики использования
- Дашборды с виджетами заменяют необходимость в отдельной OPS-панели
- Mультипанель (Filament Multi-panel) на будущее — отдельная панель учителя поверх той же кодовой базы

### Последствия

**Плюсы:** не пишем второй фронт; единая auth (Sanctum + permission); CRUD за минуты; экосистема плагинов.

**Минусы:** жёстко привязаны к Laravel — если когда-то будет отдельный SPA-админ, мигрировать придётся целиком; Filament-стиль (Tailwind + Livewire) — не подходит для публичного фронта (его делаем на Quasar).

**Связь с мониторингом:** OPS-метрики (slow queries, queue depth) закрываются Laravel Pulse как отдельная встроенная панель. Filament — для бизнес-данных.

---

## English

### Context

Need an admin UI: CRUD over content/users, moderation, system dashboards. Solo developer.

Options: separate admin service, **Filament 3**, third-party SaaS.

### Decision

**Filament 3** inside the monolith as the `Admin` module. Access via `admin` role through `spatie/laravel-permission`. Future multi-panel for teachers.

### Consequences

**Pros:** no second frontend; unified auth; CRUD in minutes.

**Cons:** Laravel-locked; not suitable for the public frontend (Quasar).

**Ops metrics:** handled by Laravel Pulse, complementing Filament's business CRUD.
