<?php

namespace App\Modules\LearningPath\Http\Controllers;

use App\Modules\LearningPath\Http\Requests\TrackRequest;
use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class SetPrimaryTrackController extends ApiController
{
    public function __invoke(TrackRequest $request, LearningPathService $learningPath): JsonResponse
    {
        return $this->respond(
            $learningPath->setPrimaryTrack($request->user(), $request->track()),
        );
    }
}
