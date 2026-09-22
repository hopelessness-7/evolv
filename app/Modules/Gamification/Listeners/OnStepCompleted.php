<?php

namespace App\Modules\Gamification\Listeners;

use App\Models\User;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Services\GamificationService;
use App\Modules\LearningPath\Events\StepCompleted;

class OnStepCompleted
{
    public function __construct(
        private readonly GamificationService $gamification,
    ) {}

    public function handle(StepCompleted $event): void
    {
        $user = User::query()->find($event->userId);

        if ($user === null) {
            return;
        }

        $this->gamification->awardXp(
            $user,
            XpSource::StepComplete,
            [
                'step_id' => $event->stepId,
                'node_slug' => $event->nodeSlug,
                'puzzle_pieces' => $event->puzzlePieces,
            ],
            $event->puzzlePieces,
        );
    }
}
