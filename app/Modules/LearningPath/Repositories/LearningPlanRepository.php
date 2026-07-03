<?php

namespace App\Modules\LearningPath\Repositories;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\Enums\PlanStatus;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\Shared\Services\PrimaryTrackResolver;

class LearningPlanRepository implements LearningPlanRepositoryInterface
{
    public function __construct(
        private readonly PrimaryTrackResolver $primaryTrack,
    ) {}

    public function findActiveForUser(User $user, ?Track $track = null): ?LearningPlan
    {
        $track ??= $this->primaryTrack->resolve($user);

        return $this->findActiveForUserByTrack($user, $track);
    }

    public function findActiveForUserByTrack(User $user, Track $track): ?LearningPlan
    {
        return LearningPlan::query()
            ->with(['steps.node'])
            ->where('user_id', $user->id)
            ->where('track', $track->value)
            ->where('status', PlanStatus::Active)
            ->first();
    }
}
