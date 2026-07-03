# LearningPath — дизайн / Design

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

Модуль **LearningPath** — персональный маршрут: `learning_plans`, `learning_plan_steps`.

### API (v1)

```
GET  /api/v1/learning-path
GET  /api/v1/learning-path/progress
GET  /api/v1/learning-path/current-step
POST /api/v1/learning-path/steps/{id}/start
POST /api/v1/learning-path/steps/{id}/complete
```

`LearningPathReaderInterface::nextAvailableNode()` — для **Coach**.

---

<a id="english"></a>

## English

LearningPath persists ordered steps with locked/available/completed statuses.
