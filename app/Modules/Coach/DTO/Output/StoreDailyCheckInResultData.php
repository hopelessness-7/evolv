<?php

namespace App\Modules\Coach\DTO\Output;

use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class StoreDailyCheckInResultData implements RespondsAsArray
{
    public function __construct(
        public DailyCheckInData $checkIn,
        public DailyPlanData $dailyPlan,
    ) {}

    public function toArray(): array
    {
        return [
            'check_in' => $this->checkIn->toArray(),
            'daily_plan' => $this->dailyPlan->toArray(),
        ];
    }
}
