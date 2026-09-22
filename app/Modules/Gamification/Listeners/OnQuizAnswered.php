<?php

namespace App\Modules\Gamification\Listeners;

use App\Models\User;
use App\Modules\Content\Events\QuizAnswered;
use App\Modules\Gamification\Enums\XpSource;
use App\Modules\Gamification\Services\GamificationService;

class OnQuizAnswered
{
    public function __construct(
        private readonly GamificationService $gamification,
    ) {}

    public function handle(QuizAnswered $event): void
    {
        if (! $event->correct) {
            return;
        }

        $user = User::query()->find($event->userId);

        if ($user === null) {
            return;
        }

        $this->gamification->awardXp(
            $user,
            XpSource::QuizCorrect,
            [
                'node_slug' => $event->nodeSlug,
                'atom_id' => $event->atomId,
            ],
        );
    }
}
