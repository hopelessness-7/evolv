<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class LearningPlanData implements RespondsAsArray
{
    /**
     * @param  list<PlanStepData>  $steps
     */
    public function __construct(
        public int $id,
        public string $track,
        public string $status,
        public ?string $activatedAt,
        public array $steps,
    ) {}

    public static function fromModel(LearningPlan $plan): self
    {
        return new self(
            id: $plan->id,
            track: $plan->track,
            status: $plan->status->value,
            activatedAt: $plan->activated_at?->toIso8601String(),
            steps: $plan->steps
                ->map(fn ($step) => PlanStepData::fromModel($step))
                ->values()
                ->all(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'track' => $this->track,
            'status' => $this->status,
            'activated_at' => $this->activatedAt,
            'steps' => array_map(fn (PlanStepData $step) => $step->toArray(), $this->steps),
        ];
    }
}
