# 0001. Монорепо / Monorepo

- **Status:** Accepted
- **Date:** 2026-05-23

---

## Русский

### Контекст

Evolv — четыре backend-сервиса (gateway, core, llm-worker, practice-runner), общая инфраструктура (nginx, postgres, redis, ollama), Docker-образы песочниц и документация. Проект ведёт в основном один разработчик.

Варианты: монорепо, мультирепо, meta-repo с submodules.

### Решение

Один репозиторий **monorepo** на GitHub: `hopelessness-7/evolv`.

- Сервисы в `services/`
- Контракты и сиды в `shared/`
- Один `docker-compose.yml` и `Makefile` для локальной разработки
- ADR и архитектура в `docs/`

### Последствия

**Плюсы:** один `git clone`, PR может менять gateway и core вместе, единый `make smoke`, меньше операционной нагрузки.

**Минусы:** CI нужно настраивать по путям (позже); репозиторий будет расти — для масштаба проекта приемлемо.

**Нейтрально:** frontend может жить в отдельной репе, связь через `APP_URL` / API в `.env`.

---

## English

### Context

Evolv has four backend services, shared infra, sandbox images, and docs. Maintained primarily by one developer.

Options: monorepo, multi-repo, meta-repo with submodules.

### Decision

Single **monorepo** at `hopelessness-7/evolv` with `services/`, `shared/`, unified compose/Makefile, and `docs/adr/`.

### Consequences

**Pros:** single clone, cross-service PRs, unified smoke tests, lower ops overhead.

**Cons:** path-aware CI later; repo size grows over time.

**Neutral:** frontend may stay separate; linked via API URL in `.env`.
