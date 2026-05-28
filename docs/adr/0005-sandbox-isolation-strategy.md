# 0005. Стратегия изоляции песочниц / Sandbox isolation strategy

- **Status:** Accepted
- **Date:** 2026-05-23

---

## Русский

### Контекст

Практика требует запуска недоверенного кода (Node + Jest) в изоляции: сессия по запросу, idle shutdown ~10 мин, JSON-вердикт, локальный Docker.

Варианты: DinD; монтирование **docker.sock**; удалённый executor (Firecracker, E2B).

### Решение

**MVP: docker.sock** в контейнере `practice-runner`.

Правила создания контейнера:

- Образ `evolv/node-learn:latest`, пользователь `learner` (non-root)
- Лимиты RAM/CPU, **без сети** (`NetworkMode: none`)
- Автоудаление при stop; sweeper по `SANDBOX_IDLE_TIMEOUT_MIN`
- Без privileged и без монтирования хостовых путей в песочницу (кроме volume сессии)

**Не в MVP:** gVisor, Kata, warm pool.

### Последствия

**Плюсы:** быстрый локальный dev, соответствует спеке «Docker on-demand».

**Минусы:** компромисс docker.sock — **риск для prod** при взломе runner; для локалки и закрытой беты допустимо.

**Prod (позже):** отдельные sandbox-ноды, лимиты сессий, gVisor или E2B/Fly при росте стоимости self-host.

---

## English

### Context

Untrusted learner code in isolated on-demand containers with idle shutdown and test verdicts.

### Decision

**MVP: host Docker socket** mount into practice-runner. Non-root image, resource limits, no network, idle sweeper. No gVisor/warm pool yet.

### Consequences

**Pros:** simplest local path.

**Cons:** socket mount is a security risk if runner is compromised — OK for dev/closed beta only.

**Production:** dedicated sandbox nodes, quotas, gVisor or managed sandbox APIs before public untrusted users.
