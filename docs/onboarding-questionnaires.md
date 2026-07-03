# Onboarding — спецификация опросников v2

Цель: собрать **минимум вопросов** с **максимумом сигнала** для Coach, LearningPath и LLM-промптов.

## Принципы дизайна

1. **Каждый вопрос → facet и/или tag** — нет «мёртвых» полей.
2. **Lite = 6–12 вопросов**, Extended = 8–15 — проход за одну сессию или с resume.
3. **Промпт-переменные** в seed (`prompt_templates`) совпадают с ключами `facets` после `AnswerInterpreter`.
4. **tags** — плоские строки для графа/поиска: `craft:beginner`, `topic:algorithms`, `mind:focus`.
5. **Не диагностика** — wellbeing/presence с disclaimer; самооценки 1–5, не клиника.

## Поток фаз

```
core (обязательно)
  → pillar_lite по enabled_pillars (craft_lite обязателен если craft включён)
  → extended по веткам (mind_focus, mind_habits, …)
```

---

## 1. Core (`core` v2.0.0)

**~3 мин.** Контекст для всех столпов и тон Coach.

| id | type | label (RU) | Зачем для LLM |
|----|------|------------|---------------|
| `display_name` | text | Как к тебе обращаться? | Персонализация, `{{display_name}}` |
| `timezone` | timezone | Часовой пояс | Расписание, напоминания |
| `interface_language` | single_select | Язык интерфейса и ответов Coach | ru / en / both |
| `daily_minutes` | number 5–480 | Минут в день на развитие | Бюджет плана |
| `weekly_days` | single_select | Сколько дней в неделю реально? | 2–3 / 4–5 / 6–7 / irregular |
| `best_time_of_day` | single_select | Когда проще учиться? | morning / afternoon / evening / night / flexible |
| `enabled_pillars` | multi_select | Что развиваем? | craft / mind / presence |
| `primary_motivation` | single_select | Главный драйвер сейчас | career_change / skill_up / wellbeing / curiosity / comeback |
| `coach_tone` | single_select | Тон Coach | supportive / direct / playful / academic |

### facets (после AnswerInterpreter)

```php
[
    'display_name' => string,
    'timezone' => string,
    'interface_language' => string,
    'daily_minutes' => int,
    'weekly_days' => string,
    'best_time_of_day' => string,
    'enabled_pillars' => string[],
    'primary_motivation' => string,
    'coach_tone' => string,
    'weekly_minutes_estimate' => int,  // daily_minutes * avg days
]
```

`weekly_minutes_estimate`: `daily_minutes * 3` для 2-3, `* 4.5` для 4-5, `* 6` для 6-7, `* 3` для irregular.

### tags

`pillar:craft`, `pillar:mind`, `motivation:career_change`, `tone:supportive`, `schedule:morning`, …

### prompt_templates

- `coach_system` — роль + имя + тон + мотивация + бюджет
- `scheduling_hint` — timezone + лучшее время + дни/неделю
- `user_context` — краткий портрет в 2–3 предложения (собирается из facets)

---

## 2. Craft Lite (`craft_lite` v2.0.0)

**~5–7 мин.** Стартовая точка в программировании.

| id | type | options / notes |
|----|------|-----------------|
| `experience_level` | single_select | absolute_beginner, beginner, junior, middle, senior |
| `years_coding` | single_select | none, under_1, 1_2, 3_5, over_5 |
| `current_stack` | multi_select | php, javascript, typescript, python, go, java, csharp, sql, none |
| `target_languages` | multi_select | php, javascript, typescript, python, go, java, csharp, rust, sql |
| `target_topics` | multi_select | fundamentals, algorithms, web_backend, web_frontend, mobile, devops, databases, testing, system_design |
| `learning_goal` | single_select | first_job, job_switch, promotion, interview, pet_project, freelance, curiosity |
| `goal_deadline` | single_select | none, 1_month, 3_months, 6_months, 1_year |
| `learning_style` | single_select | practice_first, theory_first, mixed, project_based |
| `session_length` | single_select | micro_15, standard_30, deep_45, deep_60 |
| `code_comfort` | single_select | never, tutorials_only, small_scripts, production_experience |
| `biggest_blocker` | single_select | time, motivation, where_to_start, imposter_syndrome, syntax_fear, none |
| `prefers_challenges` | single_select | yes, no, depends |

### facets

```php
[
    'experience_level' => string,
    'experience_label' => string,       // человекочитаемый
    'years_coding' => string,
    'current_stack' => string[],
    'target_languages' => string[],
    'target_topics' => string[],
    'learning_goal' => string,
    'goal_deadline' => string,
    'learning_style' => string,
    'session_length' => string,
    'code_comfort' => string,
    'biggest_blocker' => string,
    'prefers_challenges' => string,
    'difficulty_band' => string,        // beginner | intermediate | advanced — вычисляемый
    'path_priority' => string,          // interview | project | fundamentals — вычисляемый
]
```

**Вычисляемые поля (логика в AnswerInterpreter):**

- `difficulty_band`: absolute_beginner/beginner → beginner; junior → intermediate; middle/senior → advanced
- `path_priority`: interview → interview; pet_project/freelance → project; иначе fundamentals

### tags

`craft:beginner`, `lang:php`, `topic:algorithms`, `style:practice_first`, `blocker:time`, `goal:interview`, …

### prompt_templates

- `coach_system` — уровень + цель + дедлайн + стиль + блокер
- `craft_path_hint` — языки + темы + difficulty_band
- `practice_hint` — session_length + prefers_challenges + code_comfort
- `encouragement` — biggest_blocker → персональная поддержка

---

## 3. Mind Lite (`mind_lite` v2.0.0)

**~4–6 мин.** Выбор направлений + baseline (не терапия).

| id | type | notes |
|----|------|-------|
| `improvement_areas` | multi_select | focus, memory, habits, stress, energy, sleep, self_reflection, procrastination |
| `self_rating_focus` | scale_1_5 | Насколько доволен фокусом |
| `self_rating_energy` | scale_1_5 | Уровень энергии |
| `self_rating_stress` | scale_1_5 | Уровень стресса (5 = высокий) |
| `routine_stability` | single_select | chaotic, somewhat_stable, stable |
| `micro_practices_ready` | single_select | yes, later, not_sure |
| `wellbeing_disclaimer` | boolean_ack | «Evolv не заменяет специалиста» |

### facets

```php
[
    'improvement_areas' => string[],
    'self_rating_focus' => int,
    'self_rating_energy' => int,
    'self_rating_stress' => int,
    'routine_stability' => string,
    'micro_practices_ready' => string,
    'wellbeing_disclaimer_accepted' => bool,
    'suggested_extended_packs' => string[],  // mind_focus, mind_habits, …
    'mind_priority' => string,               // top area by ratings + selection
]
```

**suggested_extended_packs** (маппинг):

| area | pack code |
|------|-----------|
| focus | mind_focus |
| habits, procrastination | mind_habits |
| memory | mind_cognitive |
| stress, self_reflection | mind_wellbeing |
| energy, sleep | mind_rhythm |

### tags

`mind:focus`, `mind:stress_high`, `routine:chaotic`, `pack:mind_focus`, …

### prompt_templates

- `mind_coach_system` — области + рейтинги + готовность к микро-практикам
- `mind_daily_hint` — mind_priority + routine_stability

---

## 4. Presence Lite (`presence_lite` v2.0.0)

**~3 мин.** Формат живых сессий.

| id | type | options |
|----|------|---------|
| `session_formats` | multi_select | code_mentor, spec_review, career_coach, wellbeing_conversation |
| `communication_mode` | single_select | text_only, voice_ok, video_ok |
| `session_language` | single_select | ru, en, both |
| `session_frequency` | single_select | weekly, biweekly, on_demand |
| `comfort_sharing` | single_select | low, medium, high |
| `presence_disclaimer` | boolean_ack | wellbeing_conversation — не терапия |

### facets

Все id → facets 1:1 + `primary_format` (первый из session_formats).

### tags

`presence:code_mentor`, `presence:voice`, `comfort:medium`, …

---

## 5. Mind Extended (v2.0.0 — seeded)

| code | когда предлагать | фокус |
|------|------------------|-------|
| `mind_focus` | improvement_areas содержит focus | отвлечения, deep work, pomodoro |
| `mind_habits` | habits / procrastination | streaks, триггеры, восстановление |
| `mind_cognitive` | memory | SRS, типы упражнений |
| `mind_wellbeing` | stress / self_reflection | mood baseline, journal |
| `mind_rhythm` | energy / sleep | хронотип, режим |

Seed: `database/seeders/data/onboarding/mind_*.v2.json`. Интерпретатор: `MindExtendedAnswerInterpreter`.

---

## Порядок реализации сервисов

1. `AnswerInterpreter` — `interpretCore()`, затем `interpretCraftLite()`
2. `PromptComposer` — `render()`
3. `ProfileFacetMerger` — убрать fallback на `raw`
4. `QuestionnaireSelector` — extended-ветки
5. `ProgressEvaluator` — `is_complete` по required из selector

См. также: [onboarding-dev-guide.md](onboarding-dev-guide.md)
