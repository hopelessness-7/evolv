<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\Curriculum\DTO\Output\NodeData;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\LearningPath\Models\LearningPlanStep;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class PlanStepData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public int $order,
        public string $status,
        public ?string $completedAt,
        public NodeData $node,
    ) {}

    public static function fromModel(LearningPlanStep $step): self
    {
        return new self(
            id: $step->id,
            order: $step->order_in_plan,
            status: $step->status->value,
            completedAt: $step->completed_at?->toIso8601String(),
            node: NodeData::fromModel($step->node),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'status' => $this->status,
            'completed_at' => $this->completedAt,
            'node' => $this->node->toArray(),
        ];
    }
}
