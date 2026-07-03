<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Services\NotificationPreferenceService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetNotificationPreferencesController extends ApiController
{
    public function __invoke(Request $request, NotificationPreferenceService $preferences): JsonResponse
    {
        return $this->respond($preferences->get($request->user()));
    }
}
