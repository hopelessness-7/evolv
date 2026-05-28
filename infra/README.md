# Infrastructure / Инфраструктура

Конфигурация Docker и runtime для локальной разработки.

Docker and runtime configuration for local development.

| Путь / Path | Назначение / Purpose |
|-------------|----------------------|
| `nginx/` | Reverse proxy |
| `postgres/init/` | Расширения: `vector`, `pg_trgm` |
| `ollama/` | Скрипты загрузки моделей (опционально) |

Root `docker-compose.yml` — создаёшь сам по гайду (не коммить `.env`).
