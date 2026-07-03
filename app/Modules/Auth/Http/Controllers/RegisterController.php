<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\Http\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class RegisterController extends ApiController
{
    public function __invoke(RegisterRequest $request, AuthService $auth): JsonResponse
    {
        return $this->created($auth->register($request->getDto()));
    }
}
