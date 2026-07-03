<?php

namespace App\Modules\Onboarding\Services\Answers;

use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Services\Answers\Concerns\InterpretsAnswerLists;

class PresenceLiteAnswerInterpreter implements QuestionnaireAnswerInterpreterInterface
{
    use InterpretsAnswerLists;

    public function code(): string
    {
        return 'presence_lite';
    }

    public function interpret(array $answers): InterpretedAnswersData
    {
        $sessionFormats = $this->stringList($answers['session_formats'] ?? ['code_mentor']);

        if ($sessionFormats === []) {
            $sessionFormats = ['code_mentor'];
        }

        $facets = [
            'session_formats' => $sessionFormats,
            'communication_mode' => (string) ($answers['communication_mode'] ?? 'text_only'),
            'session_language' => (string) ($answers['session_language'] ?? 'ru'),
            'session_frequency' => (string) ($answers['session_frequency'] ?? 'on_demand'),
            'comfort_sharing' => (string) ($answers['comfort_sharing'] ?? 'medium'),
            'presence_disclaimer_accepted' => $this->boolAck($answers['presence_disclaimer'] ?? false),
            'primary_format' => $sessionFormats[0],
        ];

        return new InterpretedAnswersData(
            facets: $facets,
            tags: $this->buildTags($facets),
        );
    }

    /**
     * @param  array<string, mixed>  $facets
     * @return list<string>
     */
    private function buildTags(array $facets): array
    {
        $tags = $this->tagList('presence:', $facets['session_formats']);

        $tags[] = match ($facets['communication_mode']) {
            'voice_ok' => 'presence:voice',
            'video_ok' => 'presence:video',
            default => 'presence:text',
        };

        $tags[] = 'comfort:'.$facets['comfort_sharing'];
        $tags[] = 'frequency:'.$facets['session_frequency'];
        $tags[] = 'language:'.$facets['session_language'];

        return $tags;
    }
}
