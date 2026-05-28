# Shared contracts / Общие контракты

Фрагменты OpenAPI и JSON Schema для обмена между сервисами.

OpenAPI fragments and JSON schemas exchanged between services.

Планируемые файлы (фаза 2+ bootstrap):

Planned files (bootstrap phase 2+):

- `gateway-core-proxy.yaml` — заголовки и ошибки gateway → core
- `core-runner-sessions.yaml` — жизненный цикл сессии практики
- `llm-jobs.schema.json` — payload очереди Redis `evolv:llm:jobs`

Пока сервисы не созданы — см. [docs/architecture.md](../../docs/architecture.md) и [docs/data-model.md](../../docs/data-model.md).

Until services exist, see architecture and data-model docs.
