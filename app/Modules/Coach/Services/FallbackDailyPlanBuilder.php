<?php

namespace App\Modules\Coach\Services;

use App\Models\User;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\Enums\PlanMode;
use App\Modules\Coach\Enums\PlanSource;
use App\Modules\Coach\Enums\PlanStepType;
use App\Modules\LearningPath\Contracts\LearningPathReaderInterface;
use App\Modules\Onboarding\DTO\Output\OnboardingCoachContextData;

class FallbackDailyPlanBuilder
{
    public function __construct(
        private readonly LearningPathReaderInterface $learningPath,
        private readonly \App\Modules\Practice\Contracts\PracticeExerciseReaderInterface $exercises,
    ) {}
    public function build(string $date, User $user, OnboardingCoachContextData $context): DailyPlanData
    {
        $mode = $context->personalizedPlanEligible ? PlanMode::Personalized : PlanMode::Simplified;
        $dailyMinutes = (int) ($context->profileSummary['daily_minutes'] ?? 30);
        $displayName = $this->displayName($context);
        $reminders = $this->buildReminders($context);
        $steps = $this->buildSteps($mode, $user, $context, $dailyMinutes, $reminders);

        return DailyPlanData::fresh(
            date: $date,
            mode: $mode,
            source: PlanSource::Fallback,
            totalMinutes: $dailyMinutes,
            greeting: $this->greeting($displayName, $mode),
            steps: $steps,
            reminders: $reminders,
        );
    }

    private function displayName(OnboardingCoachContextData $context): string
    {
        $name = $context->profileSummary['facets']['core']['display_name'] ?? null;

        return is_string($name) && $name !== '' ? $name : 'there';
    }

    private function greeting(string $displayName, PlanMode $mode): string
    {
        return match ($mode) {
            PlanMode::Simplified => "Hi {$displayName}! Let's finish setting up your learning profile today.",
            PlanMode::Personalized => "Good day, {$displayName}! Here is your plan for today.",
        };
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
    ): array {
        if ($mode === PlanMode::Simplified) {
            return $this->simplifiedSteps($context, $dailyMinutes, $reminders);
        }

        return $this->personalizedSteps($user, $context, $dailyMinutes);
    }

    /**
     * @param  list<array<string, mixed>>  $reminders
     * @return list<array<string, mixed>>
     */
    private function simplifiedSteps(OnboardingCoachContextData $context, int $dailyMinutes, array $reminders): array
    {
        $steps = [];
        $next = $reminders[0] ?? null;

        if ($next !== null) {
            $steps[] = [
                'type' => PlanStepType::Onboarding->value,
                'title' => 'Complete onboarding',
                'description' => $next['message'],
                'minutes' => min(15, $dailyMinutes),
                'pillar' => null,
                'questionnaire_code' => $next['questionnaire_code'],
            ];
        }

        $remaining = max(5, $dailyMinutes - array_sum(array_column($steps, 'minutes')));

        $steps[] = [
            'type' => PlanStepType::Explore->value,
            'title' => 'Explore Evolv',
            'description' => 'Browse the platform while we prepare your personalized route.',
            'minutes' => $remaining,
            'pillar' => null,
        ];

        return $steps;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function personalizedSteps(User $user, OnboardingCoachContextData $context, int $dailyMinutes): array
    {
        $pillars = $context->profileSummary['enabled_pillars'] ?? ['craft'];
        $steps = [];
        $nextNode = $this->learningPath->nextAvailableNode($user);

        if (in_array('craft', $pillars, true)) {
            $lessonStep = [
                'type' => PlanStepType::Lesson->value,
                'title' => $nextNode !== null ? 'Study: '.$nextNode['title'] : 'Study your next topic',
                'description' => 'Read a short lesson aligned with your learning path.',
                'minutes' => (int) round($dailyMinutes * 0.4),
                'pillar' => 'craft',
            ];

            if ($nextNode !== null) {
                $lessonStep['node_id'] = $nextNode['id'];
                $lessonStep['node_slug'] = $nextNode['slug'];
            }

            $steps[] = $lessonStep;

            $craftStep = $this->craftFollowUpStep($nextNode, (int) round($dailyMinutes * 0.35));
            $steps[] = $craftStep;
        }

        if (in_array('mind', $pillars, true)) {
            $steps[] = [
                'type' => PlanStepType::Mind->value,
                'title' => 'Mind micro-practice',
                'description' => 'A short focus or reflection exercise.',
                'minutes' => max(5, (int) round($dailyMinutes * 0.2)),
                'pillar' => 'mind',
            ];
        }

        $steps[] = [
            'type' => PlanStepType::Reflection->value,
            'title' => 'Quick reflection',
            'description' => 'Note one thing you learned and one question to revisit.',
            'minutes' => max(5, $dailyMinutes - array_sum(array_column($steps, 'minutes'))),
            'pillar' => null,
        ];

        return $steps;
    }

    /**
     * @param  array{id: int, slug: string, title: string}|null  $nextNode
     * @return array<string, mixed>
     */
    private function craftFollowUpStep(?array $nextNode, int $minutes): array
    {
        $hasExercise = $nextNode !== null && $this->nodeHasExercise($nextNode['slug']);

        $step = [
            'type' => ($hasExercise ? PlanStepType::Practice : PlanStepType::QuizReview)->value,
            'title' => $hasExercise ? 'Coding exercise' : 'Self-check quiz',
            'description' => $hasExercise
                ? 'Solve the practice task for this topic in the sandbox.'
                : 'Answer the quiz questions in the lesson to reinforce what you learned.',
            'minutes' => $minutes,
            'pillar' => 'craft',
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
        } catch (\App\Modules\Practice\Exceptions\PracticeException) {
            return false;
        }
    }
}
