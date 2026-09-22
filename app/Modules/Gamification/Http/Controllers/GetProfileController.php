<?php

namespace App\Modules\Gamification\Http\Controllers;

use App\Modules\Gamification\Services\GamificationService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetProfileController extends ApiController
{
    public function __invoke(Request $request, GamificationService $gamification): JsonResponse
    {
        return $this->respond($gamification->getOrCreateProfile($request->user()));
    }
}
