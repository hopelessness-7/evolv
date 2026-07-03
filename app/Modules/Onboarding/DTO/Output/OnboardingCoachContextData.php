<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class OnboardingCoachContextData implements RespondsAsArray
{
    /**
     * @param  list<array{code: string, reason: string, required: bool}>  $pendingQuestionnaires
     */
    public function __construct(
        public bool $personalizedPlanEligible,
        public ?array $profileSummary,
        public OnboardingStatusData $status,
        public ?string $coachSystemPrompt,
        public array $pendingQuestionnaires,
    ) {}

    public function toArray(): array
    {
        return [
            'personalized_plan_eligible' => $this->personalizedPlanEligible,
            'profile_summary' => $this->profileSummary,
            'status' => $this->status->toArray(),
            'coach_system_prompt' => $this->coachSystemPrompt,
            'pending_questionnaires' => $this->pendingQuestionnaires,
        ];
    }
}
