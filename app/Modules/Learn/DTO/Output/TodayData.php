<?php

namespace App\Modules\Learn\DTO\Output;

use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\LearningPath\DTO\Output\PathProgressData;
use App\Modules\Onboarding\DTO\Output\OnboardingStatusData;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class TodayData implements RespondsAsArray
{
    public function __construct(
        public OnboardingStatusData $onboarding,
        public DailyPlanData $dailyPlan,
        public ?PathProgressData $progress,
    ) {}

    public function toArray(): array
    {
        return [
            'onboarding' => $this->onboarding->toArray(),
            'daily_plan' => $this->dailyPlan->toArray(),
            'progress' => $this->progress?->toArray(),
        ];
    }
}
