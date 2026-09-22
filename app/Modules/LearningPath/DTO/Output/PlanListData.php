<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class PlanListData implements RespondsAsArray
{
    /**
     * @param  list<PathProgressData>  $plans
     */
    public function __construct(
        public array $plans,
        public string $primaryTrack,
    ) {}

    public function toArray(): array
    {
        return [
            'primary_track' => $this->primaryTrack,
            'plans' => array_map(
                fn (PathProgressData $plan) => $plan->toArray(),
                $this->plans,
            ),
        ];
    }

    /**
     * @param  list<LearningPlan>  $models
     */
    public static function fromModels(array $models, string $primaryTrack): self
    {
        return new self(
            plans: array_map(
                fn (LearningPlan $plan) => PathProgressData::fromPlan($plan),
                $models,
            ),
            primaryTrack: $primaryTrack,
        );
    }
}
