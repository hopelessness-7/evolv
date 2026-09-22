<?php

namespace App\Modules\Gamification\Listeners;

use App\Models\User;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Services\GamificationService;
use App\Modules\Practice\Events\AttemptAccepted;

class OnAttemptAccepted
{
    public function __construct(
        private readonly GamificationService $gamification,
    ) {}

    public function handle(AttemptAccepted $event): void
    {
        $user = User::query()->find($event->userId);

        if ($user === null) {
            return;
        }

        $this->gamification->awardXp(
            $user,
            XpSource::PracticeAccepted,
            [
                'node_id' => $event->nodeId,
                'node_slug' => $event->nodeSlug,
            ],
        );
    }
}
