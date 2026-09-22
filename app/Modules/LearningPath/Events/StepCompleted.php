<?php

namespace App\Modules\LearningPath\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StepCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * @param  list<string>  $puzzlePieces
     */
    public function __construct(
        public readonly int $userId,
        public readonly int $stepId,
        public readonly string $nodeSlug,
        public readonly array $puzzlePieces = [],
    ) {}
}
