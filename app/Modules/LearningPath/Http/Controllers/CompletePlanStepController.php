<?php

namespace App\Modules\LearningPath\Http\Controllers;

use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompletePlanStepController extends ApiController
{
    public function __invoke(Request $request, int $stepId, LearningPathService $learningPath): JsonResponse
    {
        return $this->respond($learningPath->completeStep($request->user(), $stepId));
    }
}
