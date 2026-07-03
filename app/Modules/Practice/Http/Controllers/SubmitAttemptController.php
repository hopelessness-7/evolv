<?php

namespace App\Modules\Practice\Http\Controllers;

use App\Modules\Practice\Http\Requests\SubmitAttemptRequest;
use App\Modules\Practice\Services\PracticeService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class SubmitAttemptController extends ApiController
{
    public function __invoke(string $slug, SubmitAttemptRequest $request, PracticeService $practice): JsonResponse
    {
        return $this->respond(
            $practice->submitAttempt($request->user(), $slug, $request->getDto()),
        );
    }
}
