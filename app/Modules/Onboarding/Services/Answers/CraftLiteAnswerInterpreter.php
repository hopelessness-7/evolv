<?php

namespace App\Modules\Onboarding\Services\Answers;

use App\Modules\Onboarding\Contracts\QuestionnaireAnswerInterpreterInterface;
use App\Modules\Onboarding\DTO\Output\InterpretedAnswersData;
use App\Modules\Onboarding\Services\Answers\Concerns\InterpretsAnswerLists;

class CraftLiteAnswerInterpreter implements QuestionnaireAnswerInterpreterInterface
{
    use InterpretsAnswerLists;

    public function code(): string
    {
        return 'craft_lite';
    }

    public function interpret(array $answers): InterpretedAnswersData
    {
        $experienceLevel = (string) ($answers['experience_level'] ?? 'beginner');

        $facets = [
            'experience_level' => $experienceLevel,
            'experience_label' => $this->resolveExperienceLabel($experienceLevel),
            'years_coding' => (string) ($answers['years_coding'] ?? 'none'),
            'current_stack' => $this->stringList($answers['current_stack'] ?? ['none']),
            'target_languages' => $this->stringList($answers['target_languages'] ?? ['php']),
            'target_topics' => $this->stringList($answers['target_topics'] ?? ['fundamentals']),
            'learning_goal' => (string) ($answers['learning_goal'] ?? 'curiosity'),
            'goal_deadline' => (string) ($answers['goal_deadline'] ?? 'none'),
            'learning_style' => (string) ($answers['learning_style'] ?? 'mixed'),
            'session_length' => (string) ($answers['session_length'] ?? 'standard_30'),
            'code_comfort' => (string) ($answers['code_comfort'] ?? 'never'),
            'biggest_blocker' => (string) ($answers['biggest_blocker'] ?? 'none'),
            'prefers_challenges' => (string) ($answers['prefers_challenges'] ?? 'depends'),
            'difficulty_band' => $this->resolveDifficultyBand($experienceLevel),
            'path_priority' => $this->resolvePathPriority(
                (string) ($answers['learning_goal'] ?? 'curiosity'),
            ),
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
        $tags = [
            'craft:'.$facets['difficulty_band'],
            'goal:'.$facets['learning_goal'],
            'style:'.$facets['learning_style'],
            'blocker:'.$facets['biggest_blocker'],
        ];

        foreach ($facets['target_languages'] as $lang) {
            if ($lang !== 'none') {
                $tags[] = 'lang:'.$lang;
            }
        }

        return [...$tags, ...$this->tagList('topic:', $facets['target_topics'])];
    }

    private function resolveExperienceLabel(string $level): string
    {
        return match ($level) {
            'absolute_beginner' => 'Absolute Beginner',
            'beginner' => 'Beginner',
            'junior' => 'Junior',
            'middle' => 'Middle',
            'senior' => 'Senior',
            default => 'Beginner',
        };
    }

    private function resolveDifficultyBand(string $level): string
    {
        return match ($level) {
            'absolute_beginner', 'beginner' => 'beginner',
            'junior' => 'intermediate',
            'middle', 'senior' => 'advanced',
            default => 'beginner',
        };
    }

    private function resolvePathPriority(string $goal): string
    {
        return match ($goal) {
            'interview' => 'interview',
            'pet_project', 'freelance' => 'project',
            default => 'fundamentals',
        };
    }
}
