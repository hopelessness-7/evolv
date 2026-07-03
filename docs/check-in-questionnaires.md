# Check-in опросники — черновик / Draft

**Статус:** черновик · реализация после **Notifications** + **LearningPath/Practice**

**Languages:** [Русский](#русский) · [English](#english)

---

<a id="русский"></a>

## Русский

### Зачем

Онбординг собирает **стартовый профиль** (раз в жизни / редко). Для адаптации Coach и маршрута нужны **короткие регулярные срезы** (5–10 мин):

1. пользователь сейчас **в ресурсе** или перегружен;
2. что **реально прошёл** с прошлого визита;
3. система может **смягчить план**, предложить паузу, wellbeing-подсказки.

Это не замена терапии и не медицинская диагностика.

### Два семейства опросников

| Семейство | Код (черновик) | Когда | Длительность | Цель |
|-----------|----------------|-------|--------------|------|
| **Progress check** | `checkin_progress` | после N шагов / раз в 2–3 дня | 5–7 мин | сверка прогресса, блокеры, актуальность целей |
| **Wellbeing pulse** | `checkin_wellbeing` | по триггеру Coach / перед сложной сессией | 3–5 мин | энергия, перегруз, желание продолжать |

Оба — **tier: `checkin`** (новый tier рядом с `core` / `lite` / `extended`).

### Progress check (`checkin_progress`)

**Триггеры (позже):**

- завершён урок + практика за день;
- 3 дня без `attempts`;
- пользователь сам нажал «Обновить прогресс».

**Примерные блоки вопросов (8–10):**

| id | type | смысл |
|----|------|-------|
| `since_last_visit` | single_select | что делал: уроки / практика / ничего / другое |
| `completed_topics_confidence` | scale_1_5 | насколько уверен в пройденном |
| `stuck_on` | multi_select | темы, где застрял |
| `biggest_blocker_now` | single_select | время / сложность / мотивация / … |
| `goal_still_relevant` | single_select | цель из craft_lite всё ещё актуальна |
| `preferred_pace` | single_select | ускорить / оставить / замедлить |
| `needs_review_topics` | multi_select | что повторить (из последних шагов LearningPath) |
| `optional_note` | text | свободный комментарий |

**Выход (facets):**

```php
[
    'pace_adjustment' => 'slower' | 'same' | 'faster',
    'confidence_band' => 'low' | 'medium' | 'high',
    'stuck_topics' => string[],
    'blocker' => string,
    'needs_recovery_session' => bool,
]
```

**Влияние на систему:**

- Coach: меньше минут / проще шаги при `pace_adjustment=slower`;
- LearningPath: перестановка или повтор узлов из `stuck_topics`;
- Notifications: «мягкое» напоминание вместо агрессивного streak.

### Wellbeing pulse (`checkin_wellbeing`)

**Триггеры:**

- пользователь открыл приложение после долгого перерыва;
- низкий `self_rating_energy` в mind-профиле + 2 пропущенных дня;
- перед extended-практикой или длинной сессией;
- **опционально:** Coach предлагает: «2 минуты — как ты сейчас?»

**Примерные блоки (6–8):**

| id | type | смысл |
|----|------|-------|
| `energy_now` | scale_1_5 | энергия сейчас |
| `stress_now` | scale_1_5 | стресс |
| `overload_feeling` | single_select | нет / немного / сильно перегружен |
| `motivation_to_continue` | scale_1_5 | хочется ли продолжать учиться сегодня |
| `preferred_support` | single_select | пауза / лёгкий план / совет / ничего |
| `sleep_last_night` | single_select | плохо / норм / хорошо |
| `wellbeing_disclaimer` | boolean_ack | не терапия |

**Выход (facets):**

```php
[
    'resource_state' => 'low' | 'medium' | 'high',  // вычисляемый
    'overload_level' => 'none' | 'mild' | 'high',
    'motivation_today' => int,
    'support_mode' => 'pause' | 'light_plan' | 'tips' | 'none',
]
```

**Поведение при `resource_state=low`:**

| Действие | Модуль |
|----------|--------|
| Упрощённый план на день (5–10 мин, reflection / mind) | Coach |
| In-app + email: «Сегодня берём легче» | Notifications |
| Отложить practice, предложить micro-практику Mind | LearningPath / Practice |
| Короткие советы (дыхание, прогулка, сон) — шаблоны, не LLM-диагноз | Coach / Content |

### Связь с существующей моделью

```
onboarding_questionnaires.tier = 'checkin'
onboarding_sessions            = как у lite/extended (partial save)
user_profiles.facets.checkin_progress / checkin_wellbeing
```

Сессии check-in **не блокируют** обучение — всегда можно пропустить (`skipped_at`), но Coach учитывает последний срез.

### API (черновик, v1)

Те же эндпоинты Onboarding:

```
GET  /api/v1/onboarding/questionnaires/checkin_progress
POST /api/v1/onboarding/sessions   { "questionnaire_code": "checkin_wellbeing" }
```

Плюс триггер от Coach (будущее):

```
GET /api/v1/coach/checkin-suggested   # { "suggested": "checkin_wellbeing", "reason": "..." }
```

### Уведомления

| Событие | Тип notification |
|---------|------------------|
| Доступен progress check | `checkin_suggested` (новый тип) |
| 3 дня без активности + low resource | `wellbeing_nudge` |
| После wellbeing low → лёгкий план готов | `daily_plan` (mode=recovery) |

### Порядок реализации

1. **Notifications** ✅ (inbox + email)
2. **LearningPath + Practice** — есть шаги и attempts для progress check
3. Seed `checkin_progress` + `checkin_wellbeing` JSON
4. `CheckinInterpreter` + `ResourceStateEvaluator`
5. Coach: режим `recovery` в daily plan
6. Scheduler: nudge по `best_time_of_day`

### Принципы

- Пропуск без наказания (no guilt streaks)
- Явный disclaimer в wellbeing
- Не хранить чувствительные формулировки в логах
- Версионирование опросников как у core v2.0.0

---

<a id="english"></a>

## English

Two **check-in** questionnaire families (`tier: checkin`): **progress** (what was done, blockers, pace) and **wellbeing pulse** (energy, overload, motivation). Outputs adjust Coach plan intensity, LearningPath repeats, and Notifications nudges. Reuse onboarding session machinery; implement after Notifications and learning modules. Recovery mode when `resource_state=low`: lighter plan, optional tips, no forced practice.
