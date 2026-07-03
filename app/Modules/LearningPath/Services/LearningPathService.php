<?php

namespace App\Modules\LearningPath\Services;

use App\Models\User;
use App\Modules\Content\Exceptions\ContentException;
use App\Modules\Content\Services\ContentService;
use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Contracts\LearningPathReaderInterface;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\DTO\Output\CurrentStepData;
use App\Modules\LearningPath\DTO\Output\LearningPlanData;
use App\Modules\LearningPath\DTO\Output\PathProgressData;
use App\Modules\LearningPath\DTO\Output\PlanStepData;
use App\Modules\LearningPath\DTO\Output\TrackListData;
use App\Modules\LearningPath\DTO\Output\TrackOptionData;
use App\Modules\LearningPath\Enums\PlanStatus;
use App\Modules\LearningPath\Enums\StepStatus;
use App\Modules\LearningPath\Exceptions\LearningPathException;
use App\Modules\LearningPath\Models\LearningPlan;
use App\Modules\LearningPath\Models\LearningPlanStep;
use App\Modules\Shared\Services\PrimaryTrackResolver;
use Illuminate\Support\Facades\DB;

class LearningPathService implements LearningPathReaderInterface
{
    public function __construct(
        private readonly LearningPlanRepositoryInterface $plans,
        private readonly CurriculumRouteReaderInterface $curriculumRoute,
        private readonly ContentService $content,
        private readonly PrimaryTrackResolver $primaryTrack,
        private readonly NodeRepositoryInterface $nodes,
    ) {}

    public function listAvailableTracks(): TrackListData
    {
        $tracks = array_map(function (Track $track) {
            $entrySlug = $track->value.'.intro';
            $node = $this->nodes->findBySlug($entrySlug);
            $hasContent = $node !== null && $node->status === NodeStatus::Published;

            return TrackOptionData::fromTrack($track, $hasContent);
        }, Track::cases());

        return new TrackListData($tracks);
    }

    public function getOrCreateCurrent(User $user): LearningPlanData
    {
        $plan = $this->plans->findActiveForUser($user);

        if ($plan === null) {
            $plan = $this->createPlan($user);
        }

        return LearningPlanData::fromModel($plan->loadMissing(['steps.node']));
    }

    public function startStep(User $user, int $stepId): LearningPlanData
    {
        $plan = $this->plans->findActiveForUser($user)
            ?? throw LearningPathException::stepNotFound($stepId);

        $step = $plan->steps->firstWhere('id', $stepId)
            ?? throw LearningPathException::stepNotFound($stepId);

        if ($step->status !== StepStatus::Available) {
            throw LearningPathException::stepNotStartable($stepId);
        }

        $step->update(['status' => StepStatus::InProgress]);

        return LearningPlanData::fromModel(
            $plan->fresh(['steps.node']),
        );
    }

    public function getCurrentStep(User $user, bool $withContent = false): CurrentStepData
    {
        $this->getOrCreateCurrent($user);
        $plan = $this->plans->findActiveForUser($user)
            ?? throw LearningPathException::noRouteAvailable();

        $step = $plan->steps->first(
            fn (LearningPlanStep $candidate) => $candidate->status === StepStatus::InProgress,
        ) ?? $plan->steps->first(
            fn (LearningPlanStep $candidate) => $candidate->status === StepStatus::Available,
        );

        if ($step === null) {
            return new CurrentStepData(step: null, content: null);
        }

        $stepData = PlanStepData::fromModel($step);
        $content = null;

        if ($withContent) {
            try {
                $content = $this->content->getNodeContent($step->node->slug);
            } catch (ContentException) {
                $content = null;
            }
        }

        return new CurrentStepData(step: $stepData, content: $content);
    }

    public function getProgress(User $user): PathProgressData
    {
        $this->getOrCreateCurrent($user);
        $plan = $this->plans->findActiveForUser($user)
            ?? throw LearningPathException::noRouteAvailable();

        return PathProgressData::fromPlan($plan);
    }

    public function completeStep(User $user, int $stepId): LearningPlanData
    {
        $plan = $this->plans->findActiveForUser($user)
            ?? throw LearningPathException::stepNotFound($stepId);

        $step = $plan->steps->firstWhere('id', $stepId)
            ?? throw LearningPathException::stepNotFound($stepId);

        if (! $step->isCompletable()) {
            throw LearningPathException::stepNotCompletable($stepId);
        }

        DB::transaction(function () use ($plan, $step): void {
            $step->update([
                'status' => StepStatus::Completed,
                'completed_at' => now(),
            ]);

            $next = $plan->steps
                ->first(fn (LearningPlanStep $candidate) => $candidate->order_in_plan === $step->order_in_plan + 1);

            if ($next !== null && $next->status === StepStatus::Locked) {
                $next->update(['status' => StepStatus::Available]);
            }

            $hasRemaining = $plan->steps()
                ->whereNot('status', StepStatus::Completed->value)
                ->exists();

            if (! $hasRemaining) {
                $plan->update(['status' => PlanStatus::Completed]);
            }
        });

        return LearningPlanData::fromModel(
            $plan->fresh(['steps.node']),
        );
    }

    public function nextAvailableNode(User $user): ?array
    {
        $plan = $this->plans->findActiveForUser($user);

        if ($plan === null) {
            try {
                $plan = $this->createPlan($user);
            } catch (\Throwable) {
                return null;
            }
        }

        $step = $plan->steps
            ->first(fn (LearningPlanStep $candidate) => in_array(
                $candidate->status,
                [StepStatus::Available, StepStatus::InProgress],
                true,
            ));

        if ($step === null) {
            return null;
        }

        return [
            'id' => $step->node->id,
            'slug' => $step->node->slug,
            'title' => $step->node->title,
        ];
    }

    private function createPlan(User $user): LearningPlan
    {
        $track = $this->primaryTrack->resolve($user);
        $route = $this->curriculumRoute->expandRouteForTrack($user, $track);

        if ($route->nodes === []) {
            throw LearningPathException::noRouteAvailable();
        }

        return DB::transaction(function () use ($user, $route, $track) {
            $existing = $this->plans->findActiveForUserByTrack($user, $track);

            if ($existing !== null) {
                $existing->update(['status' => PlanStatus::Archived]);
            }

            $plan = LearningPlan::query()->create([
                'user_id' => $user->id,
                'track' => $track->value,
                'status' => PlanStatus::Active,
                'activated_at' => now(),
            ]);

            foreach ($route->nodes as $index => $node) {
                LearningPlanStep::query()->create([
                    'plan_id' => $plan->id,
                    'node_id' => $node->id,
                    'order_in_plan' => $index + 1,
                    'status' => $index === 0 ? StepStatus::Available : StepStatus::Locked,
                ]);
            }

            return $plan->load(['steps.node']);
        });
    }
}
