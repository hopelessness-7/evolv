<?php

namespace App\Modules\LearningPath\Repositories;

use App\Models\User;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\Enums\PlanStatus;
use App\Modules\LearningPath\Enums\StepStatus;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\LearningPath\Models\LearningPlanStep;
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

    public function listForUser(User $user, ?PlanStatus $status = null): array
    {
        $query = LearningPlan::query()
            ->with(['steps.node'])
            ->where('user_id', $user->id)
            ->orderByDesc('activated_at')
            ->orderByDesc('id');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get()->all();
    }

    public function findStepForUser(User $user, int $stepId): ?LearningPlanStep
    {
        return LearningPlanStep::query()
            ->with(['plan.steps.node', 'node'])
            ->whereKey($stepId)
            ->whereHas('plan', fn ($q) => $q->where('user_id', $user->id))
            ->first();
    }

    public function findOpenStepForUserAndNode(User $user, int $nodeId): ?LearningPlanStep
    {
        return LearningPlanStep::query()
            ->with(['plan.steps.node', 'node'])
            ->where('node_id', $nodeId)
            ->whereIn('status', [
                StepStatus::Available,
                StepStatus::InProgress,
            ])
            ->whereHas(
                'plan',
                fn ($q) => $q
                    ->where('user_id', $user->id)
                    ->where('status', PlanStatus::Active),
            )
            ->orderBy('id')
            ->first();
    }
}
