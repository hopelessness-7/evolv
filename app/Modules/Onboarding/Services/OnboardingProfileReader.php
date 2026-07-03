<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use App\Modules\Onboarding\Contracts\OnboardingProgressEvaluatorInterface;
use App\Modules\Onboarding\Contracts\SessionRepositoryInterface;
use App\Modules\Onboarding\Contracts\UserProfileRepositoryInterface;
use App\Modules\Onboarding\DTO\Output\AvailableQuestionnaireData;
use App\Modules\Onboarding\DTO\Output\OnboardingCoachContextData;

class OnboardingProfileReader implements OnboardingProfileReaderInterface
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profiles,
        private readonly SessionRepositoryInterface $sessions,
        private readonly OnboardingProgressEvaluatorInterface $progressEvaluator,
    ) {}

    public function readForCoach(User $user): OnboardingCoachContextData
    {
        $profile = $this->profiles->firstOrCreate($user);
        $userSessions = $this->sessions->listForUser($user);
        $status = $this->progressEvaluator->evaluate($user, $profile, $userSessions);

        $completedCodes = $status->completedCodes;
        $pillars = $profile->enabled_pillars ?? ['craft'];
        $personalizedPlanEligible = in_array('core', $completedCodes, true)
            && $this->hasCompletedLiteForAnyPillar($pillars, $completedCodes);

        $pending = array_values(array_filter(
            $status->available,
            fn (AvailableQuestionnaireData $q) => ! in_array($q->code, $completedCodes, true),
        ));

        return new OnboardingCoachContextData(
            personalizedPlanEligible: $personalizedPlanEligible,
            profileSummary: [
                'timezone' => $profile->timezone,
                'daily_minutes' => $profile->daily_minutes,
                'enabled_pillars' => $profile->enabled_pillars,
                'facets' => $profile->facets,
            ],
            status: $status,
            coachSystemPrompt: $this->sessions->findLatestCoachSystemPrompt($user),
            pendingQuestionnaires: array_map(
                fn (AvailableQuestionnaireData $q) => $q->toArray(),
                $pending,
            ),
        );
    }

    /**
     * @param  list<string>  $pillars
     * @param  list<string>  $completedCodes
     */
    private function hasCompletedLiteForAnyPillar(array $pillars, array $completedCodes): bool
    {
        foreach ($pillars as $pillar) {
            $liteCode = match ($pillar) {
                'craft' => 'craft_lite',
                'mind' => 'mind_lite',
                'presence' => 'presence_lite',
                default => null,
            };

            if ($liteCode !== null && in_array($liteCode, $completedCodes, true)) {
                return true;
            }
        }

        return false;
    }
}
