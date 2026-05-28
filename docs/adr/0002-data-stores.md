# 0002. Хранилища данных / Data stores

- **Status:** Accepted
- **Date:** 2026-05-28

---

## Русский

### Контекст

Платформе нужны: реляционные данные (пользователи, прогресс, граф), векторный поиск (эмбеддинги контента и RAG-документация), полнотекстовый поиск, кэш и очереди.

Использовать одну БД под все задачи — невозможно (vector + fuzzy search не закрываются классическими СУБД на нужном уровне). Использовать пять разных — операционно тяжело.

### Решение

| Назначение | Инструмент | Обоснование |
|------------|------------|-------------|
| Основные данные | **MySQL 8** | Идиоматично Laravel/Sail, зрелые миграции, recursive CTE покрывает обход графа знаний |
| Векторы | **Qdrant** | Узкоспециализированный, фильтры по metadata, готов для RAG |
| Полнотекст | **Meilisearch** | Лёгкий, быстрый старт, официальный драйвер Laravel Scout, typo-tolerance из коробки |
| Кэш, очереди, broadcasting, сессии | **Redis 7** | Стандарт для Laravel |

Граф знаний остаётся в MySQL: на масштабе MVP (сотни узлов) recursive CTE даёт мгновенные ответы; абстракция `GraphRepository` в модуле `Curriculum` оставляет возможность подменить реализацию на Neo4j, если потребуются графовые алгоритмы.

### Последствия

**Плюсы:** каждый инструмент решает свою задачу; легко докрутить RAG; Meilisearch+Scout снимает 95% сценариев поиска.

**Минусы:** четыре data store вместо одного — больше контейнеров, больше health-чек, больше backup-стратегий; нужна синхронизация (MySQL → Qdrant эмбеддинги, MySQL → Meilisearch индекс) — закрывается асинхронными job'ами.

---

## English

### Context

Platform requires relational storage, vector search (embeddings + RAG), full-text search, cache, and queues. No single engine covers all needs well; using five different ones is operationally heavy.

### Decision

| Purpose | Tool |
|---------|------|
| Primary relational data | **MySQL 8** |
| Vector search | **Qdrant** |
| Full-text search | **Meilisearch** + Laravel Scout |
| Cache / queues / broadcast / sessions | **Redis 7** |

Knowledge graph lives in MySQL (recursive CTE is sufficient at MVP scale). `GraphRepository` abstraction in the `Curriculum` module preserves the option to switch to Neo4j later.

### Consequences

**Pros:** each tool fits its job; RAG support out of the box; Scout+Meilisearch covers 95% of search needs.

**Cons:** four data stores increase ops surface; MySQL→Qdrant and MySQL→Meilisearch sync handled via async jobs.
