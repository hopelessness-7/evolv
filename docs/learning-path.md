# LearningPath — дизайн / Design

Модуль **LearningPath** — персональные маршруты: `learning_plans`, `learning_plan_steps`.

Поддерживается **несколько активных курсов** (по одному active plan на track).

## API (v1)

```
GET  /api/v1/learning-path/tracks
GET  /api/v1/learning-path/plans
POST /api/v1/learning-path/enroll          { "track": "php" }
PUT  /api/v1/learning-path/primary         { "track": "laravel" }
GET  /api/v1/learning-path                 ?track=optional
GET  /api/v1/learning-path/progress        ?track=optional
GET  /api/v1/learning-path/current-step    ?track=optional
POST /api/v1/learning-path/steps/{id}/start
POST /api/v1/learning-path/steps/{id}/complete
```

- **enroll** — создать active plan для трека (идемпотентно, если уже есть).
- **primary** — сохранить preferred track в `user_profiles.facets.path.primary_track` (+ enroll если нужно).
- **plans** — список планов + `primary_track`.
- Step start/complete ищут step по владельцу (можно завершать шаг любого enrolled трека).

## Auto-complete

Шаг плана завершается **автоматически**, без `POST …/complete`, когда для узла выполнены оба условия:

1. **Теория** — все quiz-атомы узла отвечены верно (сохраняются как `attempts.kind=quiz`).
2. **Практика** — есть `accepted` attempt по exercise (если у узла есть exercise-атом).

Если квизов нет — теория считается выполненной. Если exercise нет — достаточно теории.

Слушатели: `QuizAnswered`, `AttemptAccepted` → `LessonStepAutoCompletion`.

`POST …/complete` остаётся для админ/тест-сценариев.

`LearningPathReaderInterface::nextAvailableNode()` — для **Coach** (primary track).
