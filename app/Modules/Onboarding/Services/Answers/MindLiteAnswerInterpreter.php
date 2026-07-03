<?php

namespace App\Modules\Onboarding\Services\Answers;

use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Services\Answers\Concerns\InterpretsAnswerLists;

class MindLiteAnswerInterpreter implements QuestionnaireAnswerInterpreterInterface
{
    use InterpretsAnswerLists;

    /** @var array<string, string> */
    private const AREA_TO_PACK = [
        'focus' => 'mind_focus',
        'habits' => 'mind_habits',
        'procrastination' => 'mind_habits',
        'memory' => 'mind_cognitive',
        'stress' => 'mind_wellbeing',
        'self_reflection' => 'mind_wellbeing',
        'energy' => 'mind_rhythm',
        'sleep' => 'mind_rhythm',
    ];

    public function code(): string
    {
        return 'mind_lite';
    }

    public function interpret(array $answers): InterpretedAnswersData
    {
        $improvementAreas = $this->stringList($answers['improvement_areas'] ?? ['focus']);

        if ($improvementAreas === []) {
            $improvementAreas = ['focus'];
        }

        $ratings = [
            'focus' => $this->intRating($answers['self_rating_focus'] ?? null),
            'energy' => $this->intRating($answers['self_rating_energy'] ?? null),
            'stress' => $this->intRating($answers['self_rating_stress'] ?? null),
        ];

        $facets = [
            'improvement_areas' => $improvementAreas,
            'self_rating_focus' => $ratings['focus'],
            'self_rating_energy' => $ratings['energy'],
            'self_rating_stress' => $ratings['stress'],
            'routine_stability' => (string) ($answers['routine_stability'] ?? 'somewhat_stable'),
            'micro_practices_ready' => (string) ($answers['micro_practices_ready'] ?? 'not_sure'),
            'wellbeing_disclaimer_accepted' => $this->boolAck($answers['wellbeing_disclaimer'] ?? false),
            'suggested_extended_packs' => $this->suggestedExtendedPacks($improvementAreas),
            'mind_priority' => $this->resolveMindPriority($improvementAreas, $ratings),
        ];

        return new InterpretedAnswersData(
            facets: $facets,
            tags: $this->buildTags($facets),
        );
    }

    /**
     * @param  list<string>  $areas
     * @return list<string>
     */
    private function suggestedExtendedPacks(array $areas): array
    {
        $packs = [];

        foreach ($areas as $area) {
            if (isset(self::AREA_TO_PACK[$area])) {
                $packs[] = self::AREA_TO_PACK[$area];
            }
        }

        return array_values(array_unique($packs));
    }

    /**
     * @param  list<string>  $areas
     * @param  array{focus: int, energy: int, stress: int}  $ratings
     */
    private function resolveMindPriority(array $areas, array $ratings): string
    {
        $scores = [];

        foreach ($areas as $area) {
            $scores[$area] = match ($area) {
                'focus' => 6 - $ratings['focus'],
                'energy' => 6 - $ratings['energy'],
                'stress' => $ratings['stress'],
                'procrastination' => 4,
                'habits' => 3,
                'sleep' => 3,
                default => 2,
            };
        }

        arsort($scores);

        return array_key_first($scores) ?? 'focus';
    }

    /**
     * @param  array<string, mixed>  $facets
     * @return list<string>
     */
    private function buildTags(array $facets): array
    {
        $tags = $this->tagList('mind:', $facets['improvement_areas']);

        if ($facets['self_rating_stress'] >= 4) {
            $tags[] = 'mind:stress_high';
        }

        if ($facets['self_rating_focus'] <= 2) {
            $tags[] = 'mind:focus_low';
        }

        if ($facets['self_rating_energy'] <= 2) {
            $tags[] = 'mind:energy_low';
        }

        $tags[] = 'routine:'.$facets['routine_stability'];
        $tags[] = 'micro_practices:'.$facets['micro_practices_ready'];

        return [...$tags, ...$this->tagList('pack:', $facets['suggested_extended_packs'])];
    }
}
