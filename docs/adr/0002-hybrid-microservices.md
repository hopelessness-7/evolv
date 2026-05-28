# 0002. Прагматичные гибридные микросервисы / Pragmatic hybrid microservices

- **Status:** Accepted
- **Date:** 2026-05-23

---

## Русский

### Контекст

В видении продукта — логические сервисы: Curriculum, Content, Coach, Practice, Interview, Progress, Jobs. Полные микросервисы с первого дня (7+ деплоев, отдельные БД, RPC) сильно замедляют одного разработчика без быстрой отдачи для пользователя.

### Решение

**Гибрид:**

| Сервис | Роль |
|--------|------|
| **gateway** (NestJS) | Auth, JWT, HTTP-прокси в core — единственная публичная точка API |
| **core** (Laravel) | Граф, контент, прогресс, оркестрация Coach — модули внутри кодовой базы |
| **llm-worker** (Python) | Ollama/API, эмбеддинги, async jobs |
| **practice-runner** (Go) | Docker SDK, жизненный цикл песочниц |

Coach, Interview, Jobs остаются **модулями или очередями в core**, пока нагрузка не потребует выноса.

### Последствия

**Плюсы:** чёткая граница безопасности (пароли не в core); Practice и LLM изолированы; MVP быстрее, чем mesh из 7 сервисов.

**Минусы:** core может разрастись — нужны границы модулей (`app/Modules/Curriculum` и т.д.).

**Дальше:** вынести interview-service на фазе 5 roadmap.

---

## English

### Context

Product vision lists many logical services. Full microservices day one slows a solo developer without faster user value.

### Decision

**Hybrid:** gateway for auth/proxy; Laravel core for business domains; Python llm-worker; Go practice-runner. Coach/Interview/Jobs stay in core until scale demands extraction.

### Consequences

**Pros:** security boundary, faster MVP than 7-service mesh.

**Cons:** core growth — enforce module boundaries.

**Follow-up:** extract interview-service in roadmap phase 5.
