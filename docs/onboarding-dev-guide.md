# Onboarding — гайд по сервисам

Инфраструктура (миграции, API, оркестратор) готова. Доменная логика — в `app/Modules/Onboarding/Services/` (см. [ADR 0007](adr/0007-repository-service-layers.md)).

Спецификация опросников: [onboarding-questionnaires.md](onboarding-questionnaires.md).

## Слои модуля

| Слой | Файлы | Кто пишет |
|------|-------|-----------|
| Controller | `Http/Controllers/*` | готово |
| **Service** | `OnboardingService` + специализированные сервисы | **вместе** |
| Repository | `Repositories/*` | готово |
| DTO | `DTO/*` | готово |

### Специализированные сервисы

| Сервис | Контракт | Задача |
|--------|----------|--------|
| `AnswerInterpreter` | `AnswerInterpreterInterface` | роутер → `Services/Answers/*` |
| `CoreAnswerInterpreter` и др. | `QuestionnaireAnswerInterpreterInterface` | answers → facets + tags по опроснику |
| `PromptComposer` | `OnboardingPromptComposerInterface` | шаблоны → промпты |
| `QuestionnaireSelector` | `QuestionnaireSelectorInterface` | какие опросники показать |
| `ProgressEvaluator` | `OnboardingProgressEvaluatorInterface` | phase, is_complete |
| `ProfileFacetMerger` | — | facets → user_profiles |
| `AnswerSchemaValidator` | `AnswerSchemaValidatorInterface` | валидация ответов по schema |

`OnboardingService` только оркестрирует: сессии, транзакции, аналитика.

## Поток данных

```
answers → AnswerInterpreter → PromptComposer → ProfileFacetMerger
                ↓
QuestionnaireSelector + ProgressEvaluator → status
```

## Порядок реализации

1. `AnswerInterpreter::interpretCore()`
2. `AnswerInterpreter::interpretCraftLite()`
3. `PromptComposer::render()`
4. `ProfileFacetMerger::applyCoreToProfile()` — убрать fallback на `raw`
5. `ProgressEvaluator` — is_complete по required
6. `QuestionnaireSelector` — extended Mind-пакеты

## Тесты

```bash
./vendor/bin/sail artisan test --filter=Onboarding
```

Unit-тесты на интерпретатор: `tests/Unit/Onboarding/AnswerInterpreterTest.php` (по желанию).
