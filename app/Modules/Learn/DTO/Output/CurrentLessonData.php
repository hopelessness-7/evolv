<?php

namespace App\Modules\Learn\DTO\Output;

use App\Modules\LearningPath\DTO\Output\CurrentStepData;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class CurrentLessonData implements RespondsAsArray
{
    public function __construct(
        public CurrentStepData $lesson,
    ) {}

    public function toArray(): array
    {
        return [
            'lesson' => $this->lesson->toArray(),
        ];
    }
}
