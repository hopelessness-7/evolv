<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Http\Requests\SaveAnswersRequest;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class SaveAnswersController extends ApiController
{
    public function __invoke(SaveAnswersRequest $request, int $sessionId, OnboardingService $onboarding): JsonResponse
    {
        return $this->respond($onboarding->saveAnswers(
            $request->user(),
            $sessionId,
            $request->getDto(),
        ));
    }
}
