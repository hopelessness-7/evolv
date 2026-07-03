<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Modules\Notifications\Services\NotificationPreferenceService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateNotificationPreferencesController extends ApiController
{
    public function __invoke(
        UpdateNotificationPreferencesRequest $request,
        NotificationPreferenceService $preferences,
    ): JsonResponse {
        return $this->respond($preferences->update($request->user(), $request->getDto()));
    }
}
