<?php

namespace App\Modules\Onboarding\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class OnboardingStatusData implements RespondsAsArray
{
    /**
     * @param  list<AvailableQuestionnaireData>  $available
     * @param  list<string>  $completedCodes
     * @param  list<string>  $inProgressCodes
     */
    public function __construct(
        public string $phase,
        public bool $isComplete,
        public array $available,
        public array $completedCodes,
        public array $inProgressCodes,
        public ?array $profileSummary,
    ) {}

    public function toArray(): array
    {
        return [
            'phase' => $this->phase,
            'is_complete' => $this->isComplete,
            'available_questionnaires' => array_map(
                fn (AvailableQuestionnaireData $item) => $item->toArray(),
                $this->available,
            ),
            'completed_questionnaires' => $this->completedCodes,
            'in_progress_questionnaires' => $this->inProgressCodes,
            'profile_summary' => $this->profileSummary,
        ];
    }
}
