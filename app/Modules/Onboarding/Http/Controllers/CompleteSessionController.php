<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompleteSessionController extends ApiController
{
    public function __invoke(Request $request, int $sessionId, OnboardingService $onboarding): JsonResponse
    {
        return $this->respond($onboarding->completeSession(
            $request->user(),
            $sessionId,
        ));
    }
}
