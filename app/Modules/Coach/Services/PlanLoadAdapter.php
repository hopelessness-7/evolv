<?php

namespace App\Modules\Coach\Services;

use App\Modules\Coach\DTO\Output\DailyCheckInData;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\Enums\PlanStepType;
use App\Modules\Shared\Support\InterfaceLanguage;

/**
 * Deterministic load adaptation from fixed check-in scales (no LLM).
 */
class PlanLoadAdapter
{
    public function apply(DailyPlanData $plan, DailyCheckInData $checkIn, string $lang): DailyPlanData
    {
        $steps = array_values(array_filter(
            $plan->steps,
            fn (array $step): bool => ($step['type'] ?? null) !== PlanStepType::CheckIn->value,
        ));

        $adapted = [];

        foreach ($steps as $step) {
            $type = (string) ($step['type'] ?? '');

            if ($checkIn->loadBand === 'low' && in_array($type, [
                PlanStepType::Practice->value,
                PlanStepType::QuizReview->value,
            ], true)) {
                continue;
            }

            $minutes = (int) ($step['minutes'] ?? 0);
            $scaled = max(0, (int) round($minutes * $checkIn->loadFactor));

            if ($checkIn->loadBand === 'low' && $type === PlanStepType::Lesson->value) {
                $scaled = max(5, $scaled);
            }

            if ($checkIn->loadBand === 'medium'
                && in_array($type, [PlanStepType::Practice->value, PlanStepType::QuizReview->value], true)
            ) {
                $scaled = max(5, $scaled);
            }

            if ($scaled <= 0 && $type !== PlanStepType::Reflection->value) {
                continue;
            }

            $step['minutes'] = max(1, $scaled);
            $adapted[] = $step;
        }

        if ($adapted === []) {
            $adapted[] = [
                'type' => PlanStepType::Reflection->value,
                'title' => InterfaceLanguage::pick($lang, [
                    'light_day_title' => [
                        'ru' => 'Лёгкий день',
                        'en' => 'Light day',
                    ],
                ], 'light_day_title'),
                'description' => InterfaceLanguage::pick($lang, [
                    'light_day_desc' => [
                        'ru' => 'Сегодня без практики — короткая рефлексия и отдых.',
                        'en' => 'No practice today — a short reflection and rest.',
                    ],
                ], 'light_day_desc'),
                'minutes' => 5,
                'pillar' => null,
                'tools' => [
                    [
                        'type' => 'notebook',
                        'label' => InterfaceLanguage::pick($lang, [
                            'tool_notebook' => [
                                'ru' => 'Записать мысль в блокнот',
                                'en' => 'Save a note to your journal',
                            ],
                        ], 'tool_notebook'),
                        'kind' => 'reflection',
                    ],
                ],
            ];
        }

        $total = array_sum(array_map(fn (array $s): int => (int) ($s['minutes'] ?? 0), $adapted));

        $greeting = $plan->greeting;
        if ($checkIn->loadBand === 'low') {
            $greeting = InterfaceLanguage::pick($lang, [
                'load_low' => [
                    'ru' => $greeting.' Сегодня укороченный план — бережём ресурс.',
                    'en' => $greeting.' Today is a lighter plan — protecting your energy.',
                ],
            ], 'load_low');
        } elseif ($checkIn->loadBand === 'medium') {
            $greeting = InterfaceLanguage::pick($lang, [
                'load_medium' => [
                    'ru' => $greeting.' Нагрузка слегка снижена по чек-ину.',
                    'en' => $greeting.' Load is slightly reduced based on your check-in.',
                ],
            ], 'load_medium');
        }

        return new DailyPlanData(
            date: $plan->date,
            mode: $plan->mode,
            source: $plan->source,
            totalMinutes: max(5, $total),
            greeting: $greeting,
            steps: $adapted,
            reminders: $plan->reminders,
            cached: $plan->cached,
            status: $plan->status,
            message: $plan->message,
            checkIn: $checkIn,
        );
    }
}
