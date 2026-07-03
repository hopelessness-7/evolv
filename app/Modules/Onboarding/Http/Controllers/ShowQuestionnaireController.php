<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ShowQuestionnaireController extends ApiController
{
    public function __invoke(string $code, OnboardingService $onboarding): JsonResponse
    {
        return $this->respond($onboarding->getQuestionnaire($code));
    }
}
