<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Http\Requests\StartSessionRequest;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StartSessionController extends ApiController
{
    public function __invoke(StartSessionRequest $request, OnboardingService $onboarding): JsonResponse
    {
        $result = $onboarding->startSession($request->user(), $request->getDto());

        return $result->resumed
            ? $this->respond($result)
            : $this->created($result);
    }
}
