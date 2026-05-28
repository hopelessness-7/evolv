# Architecture Decision Records (ADR)

Здесь фиксируются важные архитектурные решения: **почему** выбран тот или иной путь, а не только **что** сделано.

Records of significant architectural choices so future contributors understand **why**, not only **what**.

## Формат / Format

Файл: `NNNN-short-title.md`

```markdown
# NNNN. Title

- **Status:** Proposed | Accepted | Deprecated | Superseded by ADR-XXXX
- **Date:** YYYY-MM-DD

## Русский
### Контекст
### Решение
### Последствия

## English
### Context
### Decision
### Consequences
```

## Индекс / Index

| ADR | RU | EN |
|-----|----|----|
| [0001](0001-monorepo.md) | Монорепо | Monorepo |
| [0002](0002-hybrid-microservices.md) | Гибридные микросервисы | Hybrid microservices |
| [0003](0003-postgres-pgvector-over-neo4j.md) | Postgres вместо Neo4j | Postgres over Neo4j |
| [0004](0004-auth-on-gateway.md) | Auth на gateway | Auth on gateway |
| [0005](0005-sandbox-isolation-strategy.md) | Изоляция песочниц | Sandbox isolation |
