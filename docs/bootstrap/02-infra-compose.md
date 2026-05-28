# Этап 2 — Инфраструктура (docker-compose + nginx + Postgres) — выполнен

**Цель:** поднять локально Postgres (с `vector`), Redis, Ollama и nginx. Сервисы приложения подключишь на этапах 3–4.

**Ты делаешь руками.** В репозитории есть только **образцы** (`*.example`) — скопируй их, разбери по шагам, меняй под себя.

---

## Что получится в конце этапа

```text
localhost:80  →  nginx  →  (пока заглушка 502 или статика «Evolv infra OK»)
postgres:5432  —  БД evolv, расширения vector + pg_trgm
redis:6379
ollama:11434   —  LLM локально
```

Сервисы `gateway`, `core`, `llm-worker`, `practice-runner` в compose **закомментированы** до готовности Dockerfile.

---

## Шаг 0. Подготовка

```bash
cd ~/projects/evolv   # твой путь к репо
cp .env.example .env
```

В `.env` обязательно смени:

- `POSTGRES_PASSWORD`
- `INTERNAL_SERVICE_TOKEN` (длинная случайная строка)
- `JWT_ACCESS_SECRET`, `JWT_REFRESH_SECRET`

Проверка Docker:

```bash
docker compose version
docker ps
```

---

## Шаг 1. Postgres — расширения

**Создай файл** (не копируй вслепую — прочитай SQL):

`infra/postgres/init/01-extensions.sql`

Образец: [infra/postgres/init/01-extensions.sql.example](../../infra/postgres/init/01-extensions.sql.example)

```sql
CREATE EXTENSION IF NOT EXISTS vector;
CREATE EXTENSION IF NOT EXISTS pg_trgm;
```

Скрипты в `docker-entrypoint-initdb.d` выполняются **только при первом** создании volume. Если ошибся — удали volume: `docker volume rm evolv_postgres_data` (имя уточни после `docker volume ls`).

**Проверка после шага 4:**

```bash
docker compose exec postgres psql -U evolv -d evolv -c "\dx"
```

Должны быть `vector` и `pg_trgm`.

---

## Шаг 2. docker-compose.yml

**Создай** `docker-compose.yml` в корне репо.

Образец: [docker-compose.example.yml](../../docker-compose.example.yml)

Рекомендуемый порядок работы:

1. Скопируй example → `docker-compose.yml`.
2. Сначала оставь **только** сервисы: `postgres`, `redis`, `ollama`.
3. Запусти: `docker compose up -d`
4. Убедись, что все healthy: `docker compose ps`
5. Раскомментируй `nginx` — снова `docker compose up -d`
6. Сервисы приложения — когда будут Dockerfile (этапы 3–4)

### Сети (важно)

| Сеть | Кто внутри |
|------|------------|
| `evolv_internal` | postgres, redis, ollama, core, gateway, llm-worker, practice-runner |
| `evolv_public` | nginx (+ gateway, если пробросишь порт для отладки) |

`practice-runner` позже понадобит volume `docker.sock` — пока не добавляй, пока не дойдёшь до этапа practice.

### Образ Postgres

Используй образ с pgvector, например:

`pgvector/pgvector:pg16`

Не обычный `postgres:16` без vector — иначе `CREATE EXTENSION vector` упадёт.

---

## Шаг 3. Nginx

**Создай:**

- `infra/nginx/nginx.conf` ← из [nginx.conf.example](../../infra/nginx/nginx.conf.example)
- `infra/nginx/conf.d/evolv.conf` ← из [evolv.conf.example](../../infra/nginx/conf.d/evolv.conf.example)

Пока gateway нет, в `evolv.conf` можно:

- отдавать `return 200 'Evolv infra OK\n';` на `/`, или
- `proxy_pass` на заглушку — закомментированный блок в example.

Когда появится gateway — раскомментируй:

```nginx
location /api/ {
    proxy_pass http://gateway:3000;
    # proxy_set_header Host $host;
    # ...
}
```

Перезагрузка nginx после правок:

```bash
docker compose exec nginx nginx -t
docker compose exec nginx nginx -s reload
```

---

## Шаг 4. Ollama (опционально на этом шаге)

После старта контейнера:

```bash
docker compose exec ollama ollama pull nomic-embed-text
docker compose exec ollama ollama pull llama3.2
```

Первый pull долгий. Модели должны совпадать с `.env`: `OLLAMA_EMBED_MODEL`, `OLLAMA_CHAT_MODEL`.

Проверка:

```bash
curl http://localhost:11434/api/tags
```

Если порт Ollama не проброшен на хост — смотри только изнутри сети: `docker compose exec llm-worker curl ...` (когда worker будет).

---

## Шаг 5. Makefile (по желанию)

Можешь добавить цель только для инфры:

```makefile
up-infra: ## Только postgres, redis, ollama, nginx
	docker compose up -d postgres redis ollama nginx
```

Так удобнее, пока сервисы приложения ещё не собраны.

---

## Чеклист «этап 2 готов»

- [ ] `docker-compose.yml` создан тобой (не только example в git)
- [ ] `.env` с реальными секретами (не в git)
- [ ] `make up` или `docker compose up -d` — postgres, redis, ollama (+ nginx) в статусе Up
- [ ] `\dx` показывает `vector` и `pg_trgm`
- [ ] `curl http://localhost/` — ответ от nginx (200 или прокси)
- [ ] Ollama: модели скачаны (если планируешь LLM на этом этапе)
- [ ] Закоммитил: `infra/`, `docker-compose.yml` (без `.env`)

---

## Частые ошибки

| Симптом | Решение |
|---------|---------|
| `extension "vector" does not exist` | Образ postgres без pgvector → смени image |
| Init-скрипт не применился | Удалить volume postgres и поднять заново |
| nginx `host not found gateway` | Gateway ещё не в compose — используй заглушку в conf |
| Порт 80 занят | В `.env` смени `NGINX_HTTP_PORT=8080` и проброс в compose |

---

## Дальше

- **Этап 3:** Laravel в `services/core/` + подключение к postgres/redis в compose
- **Этап 4:** NestJS gateway + прокси в nginx

Когда застрянешь на конкретном файле — пришли свой `docker-compose.yml` или конфиг nginx, разберём точечно.
