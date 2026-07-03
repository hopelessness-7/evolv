<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Modules\Notifications\Http\Requests\ListNotificationsRequest;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Shared\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListNotificationsController extends ApiController
{
    public function __invoke(ListNotificationsRequest $request, NotificationService $notifications): JsonResponse
    {
        return $this->respond($notifications->list($request->user(), $request->getDto()));
    }
}
