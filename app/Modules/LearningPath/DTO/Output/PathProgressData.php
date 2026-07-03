<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\LearningPath\Enums\StepStatus;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\LearningPath\Models\LearningPlanStep;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class PathProgressData implements RespondsAsArray
{
    public function __construct(
        public int $planId,
        public string $track,
        public string $planStatus,
        public int $totalSteps,
        public int $completedSteps,
        public int $percent,
        public ?int $currentStepId,
    ) {}

    public static function fromPlan(LearningPlan $plan): self
    {
        $total = $plan->steps->count();
        $completed = $plan->steps
            ->filter(fn (LearningPlanStep $step) => $step->status === StepStatus::Completed)
            ->count();

        $current = $plan->steps->first(
            fn (LearningPlanStep $step) => in_array($step->status, [StepStatus::InProgress, StepStatus::Available], true),
        );

        return new self(
            planId: $plan->id,
            track: $plan->track,
            planStatus: $plan->status->value,
            totalSteps: $total,
            completedSteps: $completed,
            percent: $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            currentStepId: $current?->id,
        );
    }

    public function toArray(): array
    {
        return [
            'plan_id' => $this->planId,
            'track' => $this->track,
            'plan_status' => $this->planStatus,
            'total_steps' => $this->totalSteps,
            'completed_steps' => $this->completedSteps,
            'percent' => $this->percent,
            'current_step_id' => $this->currentStepId,
        ];
    }
}
