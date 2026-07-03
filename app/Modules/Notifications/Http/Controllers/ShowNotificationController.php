<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowNotificationController extends ApiController
{
    public function __invoke(Request $request, int $notificationId, NotificationService $notifications): JsonResponse
    {
        return $this->respond($notifications->show($request->user(), $notificationId));
    }
}
