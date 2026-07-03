<?php

namespace App\Modules\Notifications\Jobs;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationRepositoryInterface;
use App\Modules\Notifications\Mail\UserNotificationMail;
use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $notificationId,
    ) {}

    public function handle(NotificationRepositoryInterface $notifications): void
    {
        $notification = UserNotification::query()->find($this->notificationId);

        if ($notification === null || $notification->emailed_at !== null) {
            return;
        }

        $user = User::query()->find($notification->user_id);

        if ($user === null || $user->email === null || $user->email === '') {
            return;
        }

        Mail::to($user)->send(new UserNotificationMail($notification));

        $notification->emailed_at = now();
        $notifications->save($notification);
    }
}
