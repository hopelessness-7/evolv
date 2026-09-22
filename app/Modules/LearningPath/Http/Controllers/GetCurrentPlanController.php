<?php

namespace App\Modules\LearningPath\Http\Controllers;

use App\Modules\Curriculum\Enums\Track;
use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCurrentPlanController extends ApiController
{
    public function __invoke(Request $request, LearningPathService $learningPath): JsonResponse
    {
        $track = $this->optionalTrack($request);

        return $this->respond($learningPath->getOrCreateCurrent($request->user(), $track));
    }

    private function optionalTrack(Request $request): ?Track
    {
        $raw = $request->query('track');

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        return Track::tryFrom($raw);
    }
}
