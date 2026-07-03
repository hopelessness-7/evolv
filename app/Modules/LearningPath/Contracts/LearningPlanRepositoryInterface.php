<?php

namespace App\Modules\LearningPath\Contracts;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Models\LearningPlan;

interface LearningPlanRepositoryInterface
{
    public function findActiveForUser(User $user, ?Track $track = null): ?LearningPlan;

    public function findActiveForUserByTrack(User $user, Track $track): ?LearningPlan;
}
