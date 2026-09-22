<?php

namespace App\Modules\Coach\Http\Controllers;

use App\Modules\Coach\Http\Requests\GetDailyCheckInRequest;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class GetDailyCheckInController extends ApiController
{
    public function __invoke(GetDailyCheckInRequest $request, CoachService $coach): JsonResponse
    {
        $checkIn = $coach->getCheckIn($request->user(), $request->dateQuery());

        return response()->json([
            'check_in' => $checkIn?->toArray(),
        ]);
    }
}
