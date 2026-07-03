<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends ApiController
{
    public function __invoke(Request $request, AuthService $auth): JsonResponse
    {
        return $this->success([
            'user' => $auth->me($request->user())->toArray(),
        ]);
    }
}
