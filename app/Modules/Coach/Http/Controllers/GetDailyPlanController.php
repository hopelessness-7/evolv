<?php

namespace App\Modules\Coach\Http\Controllers;

use App\Modules\Coach\Http\Requests\GetDailyPlanRequest;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class GetDailyPlanController extends ApiController
{
    public function __invoke(GetDailyPlanRequest $request, CoachService $coach): JsonResponse
    {
        return $this->respond($coach->getDailyPlan($request->user(), $request->getDto()));
    }
}
