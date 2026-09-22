<?php

namespace App\Modules\LearningPath\Services;

use App\Models\User;
use App\Modules\Content\DTO\Output\AtomData;
use App\Modules\Content\Enums\AtomKind;
use App\Modules\Content\Events\QuizAnswered;
use App\Modules\Content\Exceptions\ContentException;
use App\Modules\Content\Services\ContentService;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\Enums\StepStatus;
use App\Modules\LearningPath\Exceptions\LearningPathException;
use App\Modules\Practice\Contracts\AttemptRepositoryInterface;
use App\Modules\Practice\Enums\AttemptKind;
use App\Modules\Practice\Enums\AttemptVerdict;
use App\Modules\Practice\Events\AttemptAccepted;
use App\Modules\Practice\Models\Attempt;
use Illuminate\Support\Facades\Log;

class LessonStepAutoCompletion
{
    public function __construct(
        private readonly LearningPathService $learningPath,
        private readonly LearningPlanRepositoryInterface $plans,
        private readonly ContentService $content,
        private readonly AttemptRepositoryInterface $attempts,
    ) {}

    public function handleQuizAnswered(QuizAnswered $event): void
    {
        if ($event->correct) {
            $this->persistQuizAttempt($event);
        }

        $this->tryAutoComplete($event->userId, $event->nodeSlug);
    }

    public function handleAttemptAccepted(AttemptAccepted $event): void
    {
        $this->tryAutoComplete($event->userId, $event->nodeSlug);
    }

    public function tryAutoComplete(int $userId, string $nodeSlug): void
    {
        $user = User::query()->find($userId);

        if ($user === null) {
            return;
        }

        try {
            $nodeContent = $this->content->getNodeContent($nodeSlug);
        } catch (ContentException) {
            return;
        }

        $nodeId = $nodeContent->node->id;
        $quizIds = $this->atomIdsByKind($nodeContent->atoms, AtomKind::Quiz);
        $hasExercise = $this->atomIdsByKind($nodeContent->atoms, AtomKind::Exercise) !== [];

        if (! $this->theoryDone($user, $nodeId, $quizIds)) {
            return;
        }

        if ($hasExercise && ! $this->attempts->hasAcceptedKind($user, $nodeId, AttemptKind::CodeExercise)) {
            return;
        }

        $step = $this->plans->findOpenStepForUserAndNode($user, $nodeId);

        if ($step === null) {
            return;
        }

        try {
            if ($step->status === StepStatus::Available) {
                $this->learningPath->startStep($user, $step->id);
            }

            $this->learningPath->completeStep($user, $step->id);
        } catch (LearningPathException $e) {
            Log::debug('Lesson auto-complete skipped', [
                'user_id' => $userId,
                'node_slug' => $nodeSlug,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    private function persistQuizAttempt(QuizAnswered $event): void
    {
        $user = User::query()->find($event->userId);

        if ($user === null) {
            return;
        }

        try {
            $nodeContent = $this->content->getNodeContent($event->nodeSlug);
        } catch (ContentException) {
            return;
        }

        $this->attempts->save(new Attempt([
            'user_id' => $user->id,
            'node_id' => $nodeContent->node->id,
            'kind' => AttemptKind::Quiz,
            'payload' => [
                'atom_id' => $event->atomId,
                'node_slug' => $event->nodeSlug,
            ],
            'verdict' => AttemptVerdict::Accepted,
            'error_tags' => [],
            'duration_ms' => 0,
            'judge0_response' => null,
        ]));
    }

    /**
     * @param  list<AtomData>  $atoms
     * @return list<int>
     */
    private function atomIdsByKind(array $atoms, AtomKind $kind): array
    {
        return array_values(array_map(
            fn (AtomData $atom) => $atom->id,
            array_filter($atoms, fn (AtomData $atom) => $atom->kind === $kind->value),
        ));
    }

    /**
     * @param  list<int>  $quizIds
     */
    private function theoryDone(User $user, int $nodeId, array $quizIds): bool
    {
        if ($quizIds === []) {
            return true;
        }

        $accepted = $this->attempts->acceptedQuizAtomIds($user, $nodeId);

        foreach ($quizIds as $quizId) {
            if (! in_array($quizId, $accepted, true)) {
                return false;
            }
        }

        return true;
    }
}
