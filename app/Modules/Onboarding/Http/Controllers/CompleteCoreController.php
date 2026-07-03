<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Http\Requests\CompleteCoreRequest;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CompleteCoreController extends ApiController
{
    public function __invoke(CompleteCoreRequest $request, OnboardingService $onboarding): JsonResponse
    {
        return $this->respond($onboarding->completeCore($request->user(), $request->getDto()));
    }
}
