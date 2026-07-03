<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationDispatcherInterface;
use App\Modules\Notifications\Contracts\NotificationPreferenceRepositoryInterface;
use App\Modules\Notifications\Contracts\NotificationRepositoryInterface;
use App\Modules\Notifications\DTO\Input\SendNotificationData;
use App\Modules\Notifications\DTO\Output\NotificationData;
use App\Modules\Notifications\Jobs\SendNotificationEmailJob;
use App\Modules\Notifications\Models\UserNotification;

class NotificationDispatcher implements NotificationDispatcherInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationPreferenceRepositoryInterface $preferences,
    ) {}

    public function send(User $user, SendNotificationData $data): NotificationData
    {
        $notification = new UserNotification([
            'user_id' => $user->id,
            'type' => $data->type,
            'title' => $data->title,
            'body' => $data->body,
            'data' => $data->data,
        ]);

        $notification = $this->notifications->save($notification);

        if ($data->sendEmail && $this->shouldSendEmail($user)) {
            SendNotificationEmailJob::dispatch($notification->id);
        }

        return NotificationData::fromModel($notification);
    }

    private function shouldSendEmail(User $user): bool
    {
        return $this->preferences->firstOrCreate($user)->email_enabled;
    }
}
