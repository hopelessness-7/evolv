<?php

namespace App\Modules\LearningPath\Contracts;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Enums\PlanStatus;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\LearningPath\Models\LearningPlanStep;

interface LearningPlanRepositoryInterface
{
    public function findActiveForUser(User $user, ?Track $track = null): ?LearningPlan;

    public function findActiveForUserByTrack(User $user, Track $track): ?LearningPlan;

    /**
     * @return list<LearningPlan>
     */
    public function listForUser(User $user, ?PlanStatus $status = null): array;

    public function findStepForUser(User $user, int $stepId): ?LearningPlanStep;

    public function findOpenStepForUserAndNode(User $user, int $nodeId): ?LearningPlanStep;
}
