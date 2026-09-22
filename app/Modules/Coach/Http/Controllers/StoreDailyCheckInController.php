<?php

namespace App\Modules\Coach\Http\Controllers;

use App\Modules\Coach\Http\Requests\StoreDailyCheckInRequest;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class StoreDailyCheckInController extends ApiController
{
    public function __invoke(StoreDailyCheckInRequest $request, CoachService $coach): JsonResponse
    {
        return $this->respond($coach->storeCheckIn($request->user(), $request->getDto()));
    }
}
