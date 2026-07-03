<?php

namespace App\Modules\Learn\Http\Controllers;

use App\Modules\Learn\Services\LearnService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTodayController extends ApiController
{
    public function __invoke(Request $request, LearnService $learn): JsonResponse
    {
        return $this->respond($learn->getToday($request->user()));
    }
}
