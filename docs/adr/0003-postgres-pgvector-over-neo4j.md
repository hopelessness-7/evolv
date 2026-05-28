# 0003. Postgres + pgvector вместо Neo4j / Postgres + pgvector over Neo4j

- **Status:** Accepted
- **Date:** 2026-05-23

---

## Русский

### Контекст

Нужен **граф знаний** (узлы, рёбра REQUIRES / RELATED_TO / IS_NEW_VERSION_OF). Neo4j хорошо подходит для обходов. На MVP — один трек, **25–40 узлов**. Плюс реляционные данные (прогресс, версии контента) и **векторные эмбеддинги** для дедупа.

### Решение

Один **Postgres 16**:

- Таблицы `knowledge_nodes`, `knowledge_edges`
- Обход — **recursive CTE**
- Расширение **`vector`** для `content_atoms.embedding`
- Расширение **`pg_trgm`** для текстового поиска

Neo4j откладываем, пока граф не вырастет существенно или обходы не станут узким местом по метрикам.

### Последствия

**Плюсы:** один бэкап, миграции Laravel, JOIN прогресса и графа в одном запросе, без третьей БД для embeddings.

**Минусы:** сложные графовые алгоритмы (PageRank и т.п.) труднее, чем в Neo4j; пишем CTE вручную.

**Миграция:** при необходимости — read-replica Neo4j, Postgres остаётся source of truth.

---

## English

### Context

Knowledge graph plus relational progress/content and vector embeddings for dedup. MVP: ~25–40 nodes.

### Decision

Single **Postgres 16** with adjacency tables, recursive CTE, `vector` and `pg_trgm` extensions. Defer Neo4j until scale or measured bottlenecks.

### Consequences

**Pros:** one DB, Laravel migrations, joins in one query.

**Cons:** heavy graph analytics harder than Neo4j/Cypher.

**Migration path:** Neo4j read replica later; Postgres remains canonical.
