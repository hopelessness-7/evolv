<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Contracts\OnboardingProgressEvaluatorInterface;
use App\Modules\Onboarding\Contracts\QuestionnaireSelectorInterface;
use App\Modules\Onboarding\DTO\Output\OnboardingStatusData;
use App\Modules\Onboarding\Enums\SessionStatus;
use App\Modules\Onboarding\Models\OnboardingSession;
use App\Modules\Onboarding\Models\UserProfile;
use Illuminate\Support\Collection;

readonly class ProgressEvaluator implements OnboardingProgressEvaluatorInterface
{
    public function __construct(private QuestionnaireSelectorInterface $selector) {}

    public function evaluate(User $user, ?UserProfile $profile, Collection $sessions): OnboardingStatusData
    {
        $completed = $sessions->where('status', SessionStatus::Completed);
        $inProgress = $sessions->where('status', SessionStatus::InProgress);

        $completedCodes = $completed
            ->map(fn (OnboardingSession $s) => $s->questionnaire_code)
            ->unique()
            ->values()
            ->all();

        $inProgressCodes = $inProgress
            ->map(fn (OnboardingSession $s) => $s->questionnaire_code)
            ->unique()
            ->values()
            ->all();

        $available = $this->selector->availableFor($user, $profile, $completed);

        $isComplete = ! collect($available)->contains(fn ($q) => $q->required);
        $phase = $this->resolvePhase($completedCodes, $available, $isComplete);

        return new OnboardingStatusData(
            phase: $phase,
            isComplete: $isComplete,
            available: $available,
            completedCodes: $completedCodes,
            inProgressCodes: $inProgressCodes,
            profileSummary: $profile ? [
                'timezone' => $profile->timezone,
                'daily_minutes' => $profile->daily_minutes,
                'enabled_pillars' => $profile->enabled_pillars,
                'facets' => $profile->facets,
            ] : null,
        );
    }

    /**
     * @param  list<string>  $completedCodes
     * @param  list<\App\Modules\Onboarding\DTO\Output\AvailableQuestionnaireData>  $available
     */
    private function resolvePhase(array $completedCodes, array $available, bool $isComplete): string
    {
        if (! in_array('core', $completedCodes, true)) {
            return 'core_pending';
        }

        if (! $isComplete) {
            return 'pillar_lite';
        }

        if ($available !== []) {
            return 'extended';
        }

        return 'complete';
    }
}
