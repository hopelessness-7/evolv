<?php

namespace App\Modules\LearningPath\Http\Controllers;

use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListTracksController extends ApiController
{
    public function __invoke(LearningPathService $learningPath): JsonResponse
    {
        return $this->respond($learningPath->listAvailableTracks());
    }
}
