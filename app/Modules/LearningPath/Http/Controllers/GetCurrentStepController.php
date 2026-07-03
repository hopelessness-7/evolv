<?php

namespace App\Modules\LearningPath\Http\Controllers;

use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetCurrentStepController extends ApiController
{
    public function __invoke(Request $request, LearningPathService $learningPath): JsonResponse
    {
        return $this->respond($learningPath->getCurrentStep($request->user(), withContent: true));
    }
}
