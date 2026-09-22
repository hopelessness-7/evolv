# Curriculum — дизайн / Design

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

Модуль **Curriculum** владеет графом знаний: `knowledge_nodes`, `knowledge_edges`.

### API (v1)

```
GET /api/v1/curriculum/nodes?track=php
GET /api/v1/curriculum/nodes/{slug}
GET /api/v1/curriculum/nodes/{slug}/prerequisites
GET /api/v1/curriculum/nodes/{slug}/related
GET /api/v1/curriculum/entry-nodes
```

`CurriculumRouteReaderInterface::expandRoute($user)` — маршрут для **LearningPath** (топологическая сортировка).

### Seed

`database/seeders/data/curriculum/php_fundamentals.json` — полный курс PHP 8+ (~28 узлов, zero→pro).

`database/seeders/data/curriculum/laravel_middle.json` — трек Laravel Middle (дыры рынка).

---

<a id="english"></a>

## English

Curriculum owns the knowledge graph. See [api.md](api.md) for Swagger.
