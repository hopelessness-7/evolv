<?php

namespace App\Modules\Practice\Http\Controllers;

use App\Modules\Practice\Services\PracticeService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class GetNodeExerciseController extends ApiController
{
    public function __invoke(string $slug, PracticeService $practice): JsonResponse
    {
        return $this->respond($practice->getExercise($slug));
    }
}
