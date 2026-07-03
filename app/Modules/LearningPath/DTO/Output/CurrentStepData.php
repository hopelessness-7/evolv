<?php

namespace App\Modules\LearningPath\DTO\Output;

use App\Modules\Content\DTO\Output\NodeContentData;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class CurrentStepData implements RespondsAsArray
{
    public function __construct(
        public ?PlanStepData $step,
        public ?NodeContentData $content,
    ) {}

    public function toArray(): array
    {
        return [
            'step' => $this->step?->toArray(),
            'content' => $this->content?->toArray(),
        ];
    }
}
