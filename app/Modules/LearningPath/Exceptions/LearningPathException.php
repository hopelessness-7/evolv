<?php

namespace App\Modules\LearningPath\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class LearningPathException extends ApiException
{
    public static function stepNotFound(int $stepId): self
    {
        return new self("Learning plan step [{$stepId}] not found.", 404, 'learning_step_not_found');
    }

    public static function noRouteAvailable(): self
    {
        return new self('No learning route could be built for this user.', 404, 'learning_route_unavailable');
    }

    public static function stepNotCompletable(int $stepId): self
    {
        return new self("Learning plan step [{$stepId}] cannot be completed.", 409, 'learning_step_not_completable');
    }

    public static function stepNotStartable(int $stepId): self
    {
        return new self("Learning plan step [{$stepId}] cannot be started.", 409, 'learning_step_not_startable');
    }

    public static function trackNotEnrollable(string $track): self
    {
        return new self("Track [{$track}] has no published curriculum entry.", 422, 'learning_track_not_enrollable');
    }

    public static function planNotFoundForTrack(string $track): self
    {
        return new self("No active learning plan for track [{$track}]. Enroll first.", 404, 'learning_plan_not_found');
    }
}
