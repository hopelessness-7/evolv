# Content — дизайн / Design

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

Модуль **Content** хранит версии уроков и атомы (`theory`, `snippet`, `quiz`, `summary`) для узлов Curriculum.

### API (v1)

```
GET  /api/v1/content/nodes/{slug}
POST /api/v1/content/nodes/{slug}/quiz-check
```

Quiz-check сравнивает ответ с `meta.answer` (без Practice/Judge0).

### Seed

`ContentSeeder` — JSON + автогенерация для всех 12 PHP-узлов.

---

<a id="english"></a>

## English

Content serves markdown lesson atoms. Swagger: [api.md](api.md).
