<?php

namespace App\Modules\Onboarding\Services\Answers;

use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Services\Answers\Concerns\InterpretsAnswerLists;

class MindExtendedAnswerInterpreter implements QuestionnaireAnswerInterpreterInterface
{
    use InterpretsAnswerLists;

    public function __construct(private readonly string $questionnaireCode) {}

    public function code(): string
    {
        return $this->questionnaireCode;
    }

    public function interpret(array $answers): InterpretedAnswersData
    {
        $facets = [
            'pack_code' => $this->questionnaireCode,
            ...$this->normalizeFacets($answers),
        ];

        return new InterpretedAnswersData(
            facets: $facets,
            tags: [
                'mind:extended',
                'pack:'.$this->questionnaireCode,
                ...$this->buildAnswerTags($answers),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return array<string, mixed>
     */
    private function normalizeFacets(array $answers): array
    {
        $facets = [];

        foreach ($answers as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value)) {
                $facets[$key] = $this->stringList($value);

                continue;
            }

            if (is_bool($value)) {
                $facets[$key] = $value;

                continue;
            }

            if (is_numeric($value)) {
                $facets[$key] = is_float($value + 0) ? (float) $value : (int) $value;

                continue;
            }

            if (is_string($value)) {
                $facets[$key] = $value;
            }
        }

        return $facets;
    }

    /**
     * @param  array<string, mixed>  $answers
     * @return list<string>
     */
    private function buildAnswerTags(array $answers): array
    {
        $tags = [];

        foreach ($answers as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (is_string($value) && $value !== '') {
                $tags[] = $key.':'.$value;
            }

            if (is_array($value)) {
                foreach ($this->stringList($value) as $item) {
                    $tags[] = $key.':'.$item;
                }
            }
        }

        return $tags;
    }
}
