# Evolv — Архитектура / Architecture

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Видение

Evolv — **живой учебник**: граф знаний, который подстраивается под ученика и сам обновляется при смене технологий. Это не линейная последовательность видео, как на Coursera или Stepik.

### Логические сервисы

| Сервис | Стек | Зона ответственности |
|--------|------|----------------------|
| **gateway** | NestJS 11, Node 24 | Публичный API: регистрация, вход, JWT, прокси в core |
| **core** | Laravel 13, PHP 8.3 | Граф учебного плана, канон контента, прогресс, оркестрация Coach |
| **llm-worker** | FastAPI, Python 3.13 | Эмбеддинги, batch-генерация, вызовы Ollama/API |
| **practice-runner** | Go 1.26 | Docker-песочницы по запросу, тесты, idle shutdown |
| **nginx** | nginx 1.27 | Reverse proxy, TLS (prod) |

Позже (не в bootstrap): **interview** (mock-собесы), отдельный **jobs**-воркер для актуализации канона.

### Хранилища данных

**Postgres** (один инстанс, несколько доменов):

1. **Граф учебного плана** — `knowledge_nodes`, `knowledge_edges` (REQUIRES, RELATED_TO, IS_NEW_VERSION_OF). Обход — recursive CTE; Neo4j на MVP нет.
2. **Канон + озеро версий** — `content_versions`, `content_atoms` с `vector(768)` для дедупа и похожести.
3. **Профиль компетенций** — `user_skills`, `user_progress`, `attempts`, `srs_cards`.
4. **Пользователи** — таблица `users` у **gateway** (схема `auth`).

**Redis** — очереди Laravel (core), BullMQ (gateway, опционально), arq (llm-worker), очередь `evolv:llm:jobs`.

**Ollama** — локально для dev (embeddings, chat); в prod — API или гибрид.

### Потоки запросов

**Авторизованный API:** Client → nginx → gateway (JWT) → core `/v1/*` (заголовки `X-User-Id`, `X-Internal-Token`) → Postgres.

**Практика:** Client → gateway → core → practice-runner → контейнер `evolv/node-learn` → jest → запись `attempts` в core.

**LLM (async):** core → Redis → llm-worker → Ollama → обновление `content_atoms` / embeddings.

### Безопасность

- **core** не проброшен на хост — снаружи только nginx и gateway.
- `INTERNAL_SERVICE_TOKEN` обязателен для вызовов gateway ↔ core и core ↔ runner.
- Песочницы: non-root, лимиты CPU/RAM, `network=none` на MVP.
- JWT: access 15 мин, refresh 7 дней, секреты только в `.env`.

### Сеть Docker Compose

- `evolv_public` — nginx, gateway (:80)
- `evolv_internal` — core, llm-worker, practice-runner, postgres, redis, ollama

`practice-runner` монтирует `docker.sock` (см. ADR-0005).

### Откладываем на потом

Frontend, Neo4j replica, Kubernetes, CI/CD, TLS prod, interview, биллинг, gVisor/Kata.

### Связанные документы

- [data-model.md](data-model.md)
- [adr/](adr/)
- [api/](api/)

---

<a id="english"></a>

## English

### Vision

Evolv is a **living textbook**: a knowledge graph that adapts to the learner and self-updates when technology changes. It is not a static sequence of videos like Coursera or Stepik.

### Logical services

| Service | Stack | Responsibility |
|---------|-------|----------------|
| **gateway** | NestJS 11, Node 24 | Public API edge: registration, login, JWT, proxy to core |
| **core** | Laravel 13, PHP 8.3 | Curriculum graph, content canon, user progress, coach orchestration |
| **llm-worker** | FastAPI, Python 3.13 | Embeddings, batch generation, Ollama/API calls |
| **practice-runner** | Go 1.26 | On-demand Docker sandboxes, test execution, idle shutdown |
| **nginx** | nginx 1.27 | Reverse proxy, TLS termination (prod) |

Future (not in bootstrap): **interview**, dedicated **jobs** worker for canon refresh.

### Data stores

**Postgres:** curriculum graph, content canon + version lake, competency profile; **auth.users** owned by gateway.

**Redis:** Laravel queues, optional BullMQ, arq queue `evolv:llm:jobs`.

**Ollama:** local dev embeddings/chat; production API or hybrid.

### Request flows

Authenticated API, practice session, and async LLM job — same diagrams as in the Russian section.

### Security boundaries

core not on host ports; internal token required; sandbox limits; JWT lifetimes in `.env`.

### Network

`evolv_public` / `evolv_internal`; practice-runner uses docker.sock (ADR-0005).

### Deferred

Frontend, Neo4j replica, K8s, CI/CD, prod TLS, interview, billing, gVisor/Kata.

### Related documents

[data-model.md](data-model.md), [adr/](adr/), [api/](api/).
