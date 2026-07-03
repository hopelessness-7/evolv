<?php

namespace App\Modules\Notifications\Contracts;

use App\Models\User;
use App\Modules\Notifications\DTO\Input\SendNotificationData;
use App\Modules\Notifications\DTO\Output\NotificationData;

interface NotificationDispatcherInterface
{
    public function send(User $user, SendNotificationData $data): NotificationData;
}
