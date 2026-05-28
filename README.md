# Evolv

Саморазвивающаяся ИИ-платформа для обучения коду — «живой учебник», а не статичный курс.

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Идея

Три столпа продукта:

| Столп | Описание |
|-------|----------|
| **Путь** | Граф знаний с зависимостями (треки: Node, SQL, …) |
| **Канон** | Одна актуальная версия темы; озеро версий для истории |
| **Coach** | План на день, подсказки, разбор ошибок, адаптивная сложность |

Индивидуальный маршрут — это **путь по общему контенту**, а не клон курса под каждого пользователя.

### Архитектура (bootstrap)

```
Client → Nginx → Gateway (NestJS 11 / Node 24) → Core (Laravel 13 / PHP 8.3)
                      ↓                              ↓
                 users (auth)                  curriculum, content, progress
                      ↓
              LLM Worker (Python 3.13)    Practice Runner (Go 1.26) → Docker sandboxes
                      ↓
                   Ollama
```

- **Postgres 16** + `pgvector` — граф, канон, прогресс, эмбеддинги
- **Redis 7** — очереди и кэш
- **Монорепо** — все сервисы в одном репозитории

Подробнее: [docs/architecture.md](docs/architecture.md) (RU/EN), [docs/adr/](docs/adr/).

### Структура репозитория

```
evolv/
├── services/
│   ├── gateway/          # NestJS — auth, JWT, прокси API
│   ├── core/             # Laravel — бизнес-логика
│   ├── llm-worker/       # FastAPI — эмбеддинги, генерация
│   └── practice-runner/  # Go — оркестрация Docker-песочниц
├── infra/                # nginx, postgres init, ollama
├── sandbox-images/       # node-learn, …
├── shared/               # контракты, сиды
└── docs/                 # архитектура, ADR, OpenAPI
```

### Требования

- Docker Desktop или Docker Engine + Compose v2
- Make
- Git

Опционально (локальная разработка одного сервиса без Docker):

- PHP 8.3, Composer
- Node 24, npm
- Python 3.13, [uv](https://docs.astral.sh/uv/)
- Go 1.26

### Быстрый старт

```bash
git clone https://github.com/hopelessness-7/evolv.git
cd evolv
cp .env.example .env
# Отредактируйте .env — POSTGRES_PASSWORD, INTERNAL_SERVICE_TOKEN, JWT secrets
```

Пошаговая сборка (инфра и сервисы своими руками): **[docs/bootstrap/](docs/bootstrap/)**

```bash
# После этапа 2 (docker-compose.yml создан по гайду):
docker compose up -d
# Этапы 3–4: make migrate, make smoke
```

### Команды Make

| Команда | Описание |
|---------|----------|
| `make up` | Запустить все сервисы |
| `make down` | Остановить контейнеры |
| `make logs` | Смотреть логи |
| `make migrate` | Применить миграции |
| `make seed` | Заполнить dev-данными |
| `make test` | Запустить тесты сервисов |
| `make build-sandbox` | Собрать образ `evolv/node-learn` |
| `make smoke` | Сквозная проверка здоровья |

### Версии стеков

| Компонент | Версия |
|-----------|--------|
| PHP | 8.3 |
| Laravel | 13 |
| Node.js | 24 LTS |
| NestJS | 11 |
| Python | 3.13 |
| FastAPI | 0.115+ |
| Go | 1.26 |
| Postgres | 16 |
| Redis | 7 |

### Лицензия

MIT — см. [LICENSE](LICENSE).

---

<a id="english"></a>

## English

Self-evolving AI platform for learning code — a living textbook, not a fixed video sequence.

### Concept

| Pillar | Description |
|--------|-------------|
| **Path** | Knowledge graph with dependencies (tracks: Node, SQL, …) |
| **Canon** | One active version per topic; version lake for history |
| **Coach** | Daily plan, hints, error analysis, adaptive difficulty |

Individual learning paths are **routes over shared content**, not cloned courses per user.

### Architecture (bootstrap)

See diagram and stack in the Russian section above (identical).

- **Postgres 16** + `pgvector` — graph, canon, progress, embeddings
- **Redis 7** — queues and cache
- **Monorepo** — all services in one repository

See [docs/architecture.md](docs/architecture.md) and [docs/adr/](docs/adr/) for details.

### Repository layout

Same tree as in the Russian section.

### Prerequisites

Docker, Make, Git; optional local runtimes: PHP 8.3, Node 24, Python 3.13, Go 1.26.

### Quick start

```bash
git clone https://github.com/hopelessness-7/evolv.git
cd evolv
cp .env.example .env
# Edit .env — set POSTGRES_PASSWORD, INTERNAL_SERVICE_TOKEN, JWT secrets

make up
make migrate
make smoke
```

### Make targets

| Command | Description |
|---------|-------------|
| `make up` | Start all services |
| `make down` | Stop containers |
| `make logs` | Follow logs |
| `make migrate` | Run DB migrations |
| `make seed` | Seed dev data |
| `make test` | Run all service tests |
| `make build-sandbox` | Build `evolv/node-learn` image |
| `make smoke` | End-to-end health check |

### Stack versions

PHP 8.3, Laravel 13, Node 24 LTS, NestJS 11, Python 3.13, FastAPI 0.115+, Go 1.26, Postgres 16, Redis 7.

### License

MIT — see [LICENSE](LICENSE).
