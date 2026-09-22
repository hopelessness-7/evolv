<?php

namespace App\Modules\Coach\Services;

use App\Models\User;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\Enums\PlanMode;
use App\Modules\Coach\Enums\PlanSource;
use App\Modules\Coach\Enums\PlanStepType;
use App\Modules\LearningPath\Contracts\LearningPathReaderInterface;
use App\Modules\Onboarding\DTO\Output\OnboardingCoachContextData;
use App\Modules\Practice\Contracts\PracticeExerciseReaderInterface;
use App\Modules\Practice\Exceptions\PracticeException;
use App\Modules\Shared\Support\InterfaceLanguage;

class FallbackDailyPlanBuilder
{
    public function __construct(
        private readonly LearningPathReaderInterface $learningPath,
        private readonly PracticeExerciseReaderInterface $exercises,
    ) {}

    public function build(string $date, User $user, OnboardingCoachContextData $context): DailyPlanData
    {
        $lang = InterfaceLanguage::fromProfileSummary($context->profileSummary);
        $mode = $context->personalizedPlanEligible ? PlanMode::Personalized : PlanMode::Simplified;
        $dailyMinutes = (int) ($context->profileSummary['daily_minutes'] ?? 30);
        $displayName = $this->displayName($context, $lang);
        $reminders = $this->buildReminders($context);
        $steps = $this->buildSteps($mode, $user, $context, $dailyMinutes, $reminders, $lang);

        return DailyPlanData::fresh(
            date: $date,
            mode: $mode,
            source: PlanSource::Fallback,
            totalMinutes: $dailyMinutes,
            greeting: $this->greeting($displayName, $mode, $lang),
            steps: $steps,
            reminders: $reminders,
        );
    }

    private function displayName(OnboardingCoachContextData $context, string $lang): string
    {
        $name = $context->profileSummary['facets']['core']['display_name'] ?? null;

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return InterfaceLanguage::pick($lang, [
            'anon' => ['ru' => 'друг', 'en' => 'there'],
        ], 'anon', 'друг');
    }

    private function greeting(string $displayName, PlanMode $mode, string $lang): string
    {
        if ($mode === PlanMode::Simplified) {
            return InterfaceLanguage::pick($lang, [
                'greet_simplified' => [
                    'ru' => "Привет, {$displayName}! Давай сегодня закончим настройку учебного профиля.",
                    'en' => "Hi {$displayName}! Let's finish setting up your learning profile today.",
                ],
            ], 'greet_simplified');
        }

        return InterfaceLanguage::pick($lang, [
            'greet_personalized' => [
                'ru' => "Добрый день, {$displayName}! Вот твой план на сегодня.",
                'en' => "Good day, {$displayName}! Here is your plan for today.",
            ],
        ], 'greet_personalized');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildReminders(OnboardingCoachContextData $context): array
    {
        $reminders = [];

        foreach ($context->pendingQuestionnaires as $questionnaire) {
            $reminders[] = [
                'type' => 'onboarding_incomplete',
                'questionnaire_code' => $questionnaire['code'],
                'required' => (bool) $questionnaire['required'],
                'message' => $questionnaire['reason'],
            ];
        }

        return $reminders;
    }

    /**
     * @param  list<array<string, mixed>>  $reminders
     * @return list<array<string, mixed>>
     */
    private function buildSteps(
        PlanMode $mode,
        User $user,
        OnboardingCoachContextData $context,
        int $dailyMinutes,
        array $reminders,
        string $lang,
    ): array {
        if ($mode === PlanMode::Simplified) {
            return $this->simplifiedSteps($dailyMinutes, $reminders, $lang);
        }

        return $this->personalizedSteps($user, $context, $dailyMinutes, $lang);
    }

    /**
     * @param  list<array<string, mixed>>  $reminders
     * @return list<array<string, mixed>>
     */
    private function simplifiedSteps(int $dailyMinutes, array $reminders, string $lang): array
    {
        $steps = [];
        $next = $reminders[0] ?? null;

        if ($next !== null) {
            $steps[] = [
                'type' => PlanStepType::Onboarding->value,
                'title' => InterfaceLanguage::pick($lang, [
                    'step_onboarding' => [
                        'ru' => 'Пройти онбординг',
                        'en' => 'Complete onboarding',
                    ],
                ], 'step_onboarding'),
                'description' => $next['message'],
                'minutes' => min(15, $dailyMinutes),
                'pillar' => null,
                'questionnaire_code' => $next['questionnaire_code'],
            ];
        }

        $remaining = max(5, $dailyMinutes - array_sum(array_column($steps, 'minutes')));

        $steps[] = [
            'type' => PlanStepType::Explore->value,
            'title' => InterfaceLanguage::pick($lang, [
                'step_explore' => [
                    'ru' => 'Познакомиться с Evolv',
                    'en' => 'Explore Evolv',
                ],
            ], 'step_explore'),
            'description' => InterfaceLanguage::pick($lang, [
                'step_explore_desc' => [
                    'ru' => 'Осмотри платформу, пока мы готовим персональный маршрут.',
                    'en' => 'Browse the platform while we prepare your personalized route.',
                ],
            ], 'step_explore_desc'),
            'minutes' => $remaining,
            'pillar' => null,
        ];

        return $steps;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function personalizedSteps(User $user, OnboardingCoachContextData $context, int $dailyMinutes, string $lang): array
    {
        $pillars = $context->profileSummary['enabled_pillars'] ?? ['craft'];
        $steps = [];
        $nextNode = $this->learningPath->nextAvailableNode($user);

        if (in_array('craft', $pillars, true)) {
            $lessonTitle = $nextNode !== null
                ? InterfaceLanguage::pick($lang, [
                    'study_named' => [
                        'ru' => 'Изучить: '.$nextNode['title'],
                        'en' => 'Study: '.$nextNode['title'],
                    ],
                ], 'study_named')
                : InterfaceLanguage::pick($lang, [
                    'study_next' => [
                        'ru' => 'Изучить следующую тему',
                        'en' => 'Study your next topic',
                    ],
                ], 'study_next');

            $lessonStep = [
                'type' => PlanStepType::Lesson->value,
                'title' => $lessonTitle,
                'description' => InterfaceLanguage::pick($lang, [
                    'study_desc' => [
                        'ru' => 'Прочитай короткий урок по текущему шагу пути.',
                        'en' => 'Read a short lesson aligned with your learning path.',
                    ],
                ], 'study_desc'),
                'minutes' => (int) round($dailyMinutes * 0.4),
                'pillar' => 'craft',
                'prompts' => [
                    InterfaceLanguage::pick($lang, [
                        'lesson_p1' => [
                            'ru' => 'Какую одну идею из урока ты объяснишь вслух за 30 секунд?',
                            'en' => 'Which one idea from the lesson can you explain aloud in 30 seconds?',
                        ],
                    ], 'lesson_p1'),
                ],
                'tools' => array_values(array_filter([
                    $nextNode !== null ? [
                        'type' => 'open_lesson',
                        'label' => InterfaceLanguage::pick($lang, [
                            'tool_open_lesson' => [
                                'ru' => 'Открыть урок',
                                'en' => 'Open lesson',
                            ],
                        ], 'tool_open_lesson'),
                        'node_slug' => $nextNode['slug'],
                    ] : null,
                    [
                        'type' => 'notebook',
                        'label' => InterfaceLanguage::pick($lang, [
                            'tool_notebook' => [
                                'ru' => 'Записать мысль в блокнот',
                                'en' => 'Save a note to your journal',
                            ],
                        ], 'tool_notebook'),
                    ],
                ])),
            ];

            if ($nextNode !== null) {
                $lessonStep['node_id'] = $nextNode['id'];
                $lessonStep['node_slug'] = $nextNode['slug'];
            }

            $steps[] = $lessonStep;
            $steps[] = $this->craftFollowUpStep($nextNode, (int) round($dailyMinutes * 0.35), $lang);
        }

        if (in_array('mind', $pillars, true)) {
            $steps[] = [
                'type' => PlanStepType::Mind->value,
                'title' => InterfaceLanguage::pick($lang, [
                    'mind_title' => [
                        'ru' => 'Микропрактика Mind',
                        'en' => 'Mind micro-practice',
                    ],
                ], 'mind_title'),
                'description' => InterfaceLanguage::pick($lang, [
                    'mind_desc' => [
                        'ru' => 'Короткое упражнение на фокус или рефлексию.',
                        'en' => 'A short focus or reflection exercise.',
                    ],
                ], 'mind_desc'),
                'minutes' => max(5, (int) round($dailyMinutes * 0.2)),
                'pillar' => 'mind',
                'prompts' => [
                    InterfaceLanguage::pick($lang, [
                        'mind_p1' => [
                            'ru' => 'Что мешало вниманию сегодня?',
                            'en' => 'What distracted you today?',
                        ],
                    ], 'mind_p1'),
                ],
                'tools' => [
                    [
                        'type' => 'notebook',
                        'label' => InterfaceLanguage::pick($lang, [
                            'tool_notebook' => [
                                'ru' => 'Записать мысль в блокнот',
                                'en' => 'Save a note to your journal',
                            ],
                        ], 'tool_notebook'),
                    ],
                ],
            ];
        }

        $steps[] = [
            'type' => PlanStepType::Reflection->value,
            'title' => InterfaceLanguage::pick($lang, [
                'reflect_title' => [
                    'ru' => 'Быстрая рефлексия',
                    'en' => 'Quick reflection',
                ],
            ], 'reflect_title'),
            'description' => InterfaceLanguage::pick($lang, [
                'reflect_desc' => [
                    'ru' => 'Ответь на три коротких вопроса и сохрани ответ в блокнот.',
                    'en' => 'Answer three short prompts and save them to your journal.',
                ],
            ], 'reflect_desc'),
            'minutes' => max(5, $dailyMinutes - array_sum(array_column($steps, 'minutes'))),
            'pillar' => null,
            'prompts' => [
                InterfaceLanguage::pick($lang, [
                    'reflect_p1' => [
                        'ru' => 'Что нового ты реально понял сегодня?',
                        'en' => 'What did you actually understand today?',
                    ],
                ], 'reflect_p1'),
                InterfaceLanguage::pick($lang, [
                    'reflect_p2' => [
                        'ru' => 'Где осталась путаница?',
                        'en' => 'Where are you still confused?',
                    ],
                ], 'reflect_p2'),
                InterfaceLanguage::pick($lang, [
                    'reflect_p3' => [
                        'ru' => 'Что применишь завтра в коде?',
                        'en' => 'What will you apply in code tomorrow?',
                    ],
                ], 'reflect_p3'),
            ],
            'tools' => [
                [
                    'type' => 'notebook',
                    'label' => InterfaceLanguage::pick($lang, [
                        'tool_notebook_reflect' => [
                            'ru' => 'Сохранить рефлексию',
                            'en' => 'Save reflection',
                        ],
                    ], 'tool_notebook_reflect'),
                    'kind' => 'reflection',
                ],
                [
                    'type' => 'self_check',
                    'label' => InterfaceLanguage::pick($lang, [
                        'tool_self_check' => [
                            'ru' => 'Короткий самочек (3 пункта)',
                            'en' => 'Quick self-check (3 items)',
                        ],
                    ], 'tool_self_check'),
                    'checklist' => [
                        InterfaceLanguage::pick($lang, [
                            'check_1' => [
                                'ru' => 'Могу объяснить тему без конспекта',
                                'en' => 'I can explain the topic without notes',
                            ],
                        ], 'check_1'),
                        InterfaceLanguage::pick($lang, [
                            'check_2' => [
                                'ru' => 'Сделал практику или квиз',
                                'en' => 'I completed practice or a quiz',
                            ],
                        ], 'check_2'),
                        InterfaceLanguage::pick($lang, [
                            'check_3' => [
                                'ru' => 'Есть вопрос на завтра',
                                'en' => 'I have a question for tomorrow',
                            ],
                        ], 'check_3'),
                    ],
                ],
            ],
        ];

        return $steps;
    }

    /**
     * @param  array{id: int, slug: string, title: string}|null  $nextNode
     * @return array<string, mixed>
     */
    private function craftFollowUpStep(?array $nextNode, int $minutes, string $lang): array
    {
        $hasExercise = $nextNode !== null && $this->nodeHasExercise($nextNode['slug']);

        $step = [
            'type' => ($hasExercise ? PlanStepType::Practice : PlanStepType::QuizReview)->value,
            'title' => InterfaceLanguage::pick($lang, [
                'practice_title' => [
                    'ru' => $hasExercise ? 'Упражнение по коду' : 'Самопроверка (квиз)',
                    'en' => $hasExercise ? 'Coding exercise' : 'Self-check quiz',
                ],
            ], 'practice_title'),
            'description' => InterfaceLanguage::pick($lang, [
                'practice_desc' => [
                    'ru' => $hasExercise
                        ? 'Реши практическое задание по этой теме в песочнице.'
                        : 'Ответь на квизы в уроке, чтобы закрепить материал.',
                    'en' => $hasExercise
                        ? 'Solve the practice task for this topic in the sandbox.'
                        : 'Answer the quiz questions in the lesson to reinforce what you learned.',
                ],
            ], 'practice_desc'),
            'minutes' => $minutes,
            'pillar' => 'craft',
            'prompts' => [
                InterfaceLanguage::pick($lang, [
                    'practice_p1' => [
                        'ru' => $hasExercise
                            ? 'Какой крайний случай ты проверил вручную?'
                            : 'Какой вариант квиза был неочевиден и почему?',
                        'en' => $hasExercise
                            ? 'Which edge case did you verify manually?'
                            : 'Which quiz option was non-obvious and why?',
                    ],
                ], 'practice_p1'),
            ],
            'tools' => array_values(array_filter([
                $nextNode !== null ? [
                    'type' => $hasExercise ? 'open_practice' : 'open_lesson',
                    'label' => InterfaceLanguage::pick($lang, [
                        'tool_open_practice' => [
                            'ru' => $hasExercise ? 'Открыть практику' : 'Открыть урок с квизом',
                            'en' => $hasExercise ? 'Open practice' : 'Open lesson quiz',
                        ],
                    ], 'tool_open_practice'),
                    'node_slug' => $nextNode['slug'],
                ] : null,
                [
                    'type' => 'notebook',
                    'label' => InterfaceLanguage::pick($lang, [
                        'tool_notebook' => [
                            'ru' => 'Записать мысль в блокнот',
                            'en' => 'Save a note to your journal',
                        ],
                    ], 'tool_notebook'),
                ],
            ])),
        ];

        if ($nextNode !== null) {
            $step['node_id'] = $nextNode['id'];
            $step['node_slug'] = $nextNode['slug'];
        }

        return $step;
    }

    private function nodeHasExercise(string $slug): bool
    {
        try {
            $this->exercises->getExercise($slug);

            return true;
        } catch (PracticeException) {
            return false;
        }
    }
}
