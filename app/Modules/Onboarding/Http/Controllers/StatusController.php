<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Http\Requests\SaveAnswersRequest;
use App\Modules\Onboarding\Http\Requests\StartSessionRequest;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends ApiController
{
    public function __invoke(Request $request, OnboardingService $onboarding): JsonResponse
    {
        return $this->respond($onboarding->getStatus($request->user()));
    }
}
