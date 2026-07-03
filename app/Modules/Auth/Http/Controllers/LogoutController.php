<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Auth\DTO\Input\LogoutData;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends ApiController
{
    public function __invoke(Request $request, AuthService $auth): JsonResponse
    {
        $auth->logout(new LogoutData(
            bearerToken: $request->bearerToken(),
        ));

        if ($request->hasSession()) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->success(['message' => 'Logged out.']);
    }
}
