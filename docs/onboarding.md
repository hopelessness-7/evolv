# Onboarding — дизайн / Design

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Зачем не один большой опрос

Пользователь приходит с разными целями: кто-то хочет только Python, кто-то — фокус и привычки, кто-то — «мягкие» сессии с коучем. Один фиксированный wizard перегружает и собирает лишние данные.

**Решение:** многоэтапный онбординг с **ветвлением** и **модульными опросниками** (questionnaire packs).

### Три фазы

| Фаза | Когда | Что собираем |
|------|-------|--------------|
| **Core** | Сразу после регистрации (обязательно) | Имя/ник, часовой пояс, дневной бюджет времени, **какие столпы включить** (Craft / Mind / Presence) |
| **Pillar Lite** | По выбранным столпам (обязательно минимум для Craft) | Краткий опрос на столп (~3–7 вопросов) |
| **Pillar Extended** | По желанию или по триггеру Coach | Углублённые опросники, можно проходить частями |

Пока не завершён **Core + Lite для хотя бы одного столпа**, Coach отдаёт упрощённый план; полный персональный маршрут — после завершения нужных lite-паков.

### Столп Craft (программирование) — Lite

Цель: понять стартовую точку и направление, без длинного теста.

Примерные блоки вопросов:

1. **Опыт** — уровень (новичок / junior / middle), годы, основной стек сейчас
2. **Цели** — что изучить (языки, темы: algorithms, web, devops…), зачем (работа, pet project, собес)
3. **Стиль** — теория vs практика, длина сессии, предпочтение: задачи / проекты / чтение
4. **Ограничения** — сколько минут в день реально (уже есть в Core, можно уточнить)

Ответы → `learning_profile` + начальные узлы графа / теги для LearningPath.

### Столп Mind (когнитивное + ментальное) — Lite vs Extended

**Lite** (при выборе столпа Mind):

- Что хочешь улучшить (мультивыбор): фокус, память, привычки/постоянство, стресс, сон/энергия, саморефлексия
- Самооценка 1–5 по выбранным областям (не клиническая диагностика)
- Готовность к ежедневным микро-практикам (да/нет/позже)

**Extended** — набор **опросников-пакетов**, подключаются по выбору в Lite:

| Пакет | Код | Когда предлагать | Примеры тем |
|-------|-----|------------------|-------------|
| Фокус и продуктивность | `mind_focus` | выбран «фокус» | отвлечения, pomodoro-опыт, лучшее время дня |
| Привычки и постоянство | `mind_habits` | выбран «привычки» | streak-опыт, провалы, триггеры |
| Когнитивные упражнения | `mind_cognitive` | память / логика | SRS-опыт, типы задач |
| Wellbeing (self-tracking) | `mind_wellbeing` | стресс / энергия | mood baseline, journal-опыт, **disclaimer: не замена терапии** |
| Сон и ритм | `mind_rhythm` | энергия / сон | режим дня, хронотип (упрощённо) |

Каждый пакет — 8–15 вопросов, можно сохранять прогресс (`session` + `answers` JSON). Пользователь может пройти 0..N пакетов; Coach напоминает о незавершённых.

### Столп Presence (сессии с «человеком») — Lite

- Какие форматы интересны: ментор по коду, ревью ТЗ, коуч, «психолог»-стиль (с disclaimer)
- Комфорт с голосом / только текст / текст + аватар
- Язык общения

Extended позже: выбор персон, сценарии первой сессии.

### Модель данных (черновик)

```mermaid
erDiagram
    users ||--o| user_profiles : has
    users ||--o{ onboarding_sessions : has
    onboarding_questionnaires ||--o{ onboarding_questions : contains
    onboarding_sessions ||--o{ onboarding_answers : has
    onboarding_sessions }o--|| onboarding_questionnaires : uses

    user_profiles {
        bigint user_id PK_FK
        string timezone
        smallint daily_minutes
        json enabled_pillars
        timestamp core_completed_at
        timestamp onboarding_completed_at
    }

    onboarding_questionnaires {
        bigint id PK
        string code UK
        string pillar
        string tier
        string version
        json schema
    }

    onboarding_sessions {
        bigint id PK
        bigint user_id FK
        bigint questionnaire_id FK
        string status
        json answers
        timestamp completed_at
    }
```

- `tier`: `core` | `lite` | `extended`
- `pillar`: `craft` | `mind` | `presence`
- `schema`: JSON Schema или свой формат (тип вопроса, options, validation) — рендер на клиенте

### API (v1)

```
GET  /api/v1/onboarding/status
GET  /api/v1/onboarding/questionnaires?pillar=craft&tier=lite
GET  /api/v1/onboarding/questionnaires/{code}
POST /api/v1/onboarding/sessions          # начать/продолжить сессию опроса
PATCH /api/v1/onboarding/sessions/{id}    # сохранить ответы (partial, с валидацией по schema)
POST /api/v1/onboarding/sessions/{id}/complete
POST /api/v1/onboarding/core              # Core wizard одним запросом
```

### MVP для первой итерации

1. Таблицы: `user_profiles`, `onboarding_questionnaires` (seed), `onboarding_sessions`
2. **Core** + **Craft/Mind/Presence Lite** + **Mind Extended** packs в seed
3. `GET status` + `POST sessions` + `complete` + `POST core`
4. Валидация ответов по schema на PATCH и complete

### Принципы

- Не медицинская диагностика; явные disclaimers в wellbeing-пакетах
- Ответы храним у нас; чувствительные поля не логируем в Telescope (уже скрываем password/token)
- Версия опросника в `questionnaires.version` — при смене вопросов не ломаем старые сессии

---

<a id="english"></a>

## English

Phased onboarding: **Core** (required) → **Pillar Lite** (short, per enabled pillar) → **Pillar Extended** (optional questionnaire packs driven by user goals). Craft lite covers experience and learning goals; Mind uses selectable packs (focus, habits, cognitive, wellbeing, rhythm). Data model: profiles + questionnaire definitions + per-user sessions with partial save. MVP ships Core + Craft lite only; Mind extended packs come next.
