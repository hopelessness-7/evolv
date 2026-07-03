<?php

namespace App\Modules\Onboarding\Services\Answers;

use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Services\Answers\Concerns\InterpretsAnswerLists;

class CoreAnswerInterpreter implements QuestionnaireAnswerInterpreterInterface
{
    use InterpretsAnswerLists;

    public function code(): string
    {
        return 'core';
    }

    public function interpret(array $answers): InterpretedAnswersData
    {
        $facets = [
            'display_name' => (string) ($answers['display_name'] ?? 'Пользователь'),
            'timezone' => (string) ($answers['timezone'] ?? 'Europe/Moscow'),
            'interface_language' => (string) ($answers['interface_language'] ?? 'ru'),
            'daily_minutes' => (int) ($answers['daily_minutes'] ?? 5),
            'weekly_days' => (string) ($answers['weekly_days'] ?? 'irregular'),
            'best_time_of_day' => (string) ($answers['best_time_of_day'] ?? 'flexible'),
            'enabled_pillars' => $this->stringList($answers['enabled_pillars'] ?? ['craft']),
            'primary_motivation' => (string) ($answers['primary_motivation'] ?? 'skill_up'),
            'coach_tone' => (string) ($answers['coach_tone'] ?? 'direct'),
            'weekly_minutes_estimate' => $this->estimateWeeklyMinutes(
                (int) ($answers['daily_minutes'] ?? 5),
                (string) ($answers['weekly_days'] ?? 'irregular'),
            ),
        ];

        if ($facets['enabled_pillars'] === []) {
            $facets['enabled_pillars'] = ['craft'];
        }

        return new InterpretedAnswersData(
            facets: $facets,
            tags: $this->buildTags($facets),
        );
    }

    private function estimateWeeklyMinutes(int $minutes, string $days): int
    {
        $multiplier = match ($days) {
            '2_3' => 3,
            '4_5' => 4.5,
            '6_7' => 6,
            default => 3,
        };

        return (int) round($minutes * $multiplier);
    }

    /**
     * @param  array<string, mixed>  $facets
     * @return list<string>
     */
    private function buildTags(array $facets): array
    {
        $tags = $this->tagList('pillar:', $facets['enabled_pillars']);

        $tags[] = 'motivation:'.$facets['primary_motivation'];
        $tags[] = 'tone:'.$facets['coach_tone'];
        $tags[] = 'schedule:'.$facets['best_time_of_day'];
        $tags[] = 'language:'.$facets['interface_language'];

        return $tags;
    }
}
