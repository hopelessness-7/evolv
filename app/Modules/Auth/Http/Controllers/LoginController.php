<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class LoginController extends ApiController
{
    public function __invoke(LoginRequest $request, AuthService $auth): JsonResponse
    {
        return $this->respond($auth->login($request->getDto()));
    }
}
