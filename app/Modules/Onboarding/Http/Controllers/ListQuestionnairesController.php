<?php

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\Onboarding\Enums\Pillar;
use App\Modules\Onboarding\Enums\Tier;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListQuestionnairesController extends ApiController
{
    public function __invoke(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $pillar = $request->query('pillar');
        $tier = $request->query('tier');

        $items = $onboarding->listQuestionnaires(
            $pillar ? Pillar::from($pillar) : null,
            $tier ? Tier::from($tier) : null,
        );

        return $this->success([
            'questionnaires' => $items->map(fn ($q) => $q->toArray())->values()->all(),
        ]);
    }
}
