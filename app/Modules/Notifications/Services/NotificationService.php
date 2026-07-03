<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationRepositoryInterface;
use App\Modules\Notifications\DTO\Input\ListNotificationsData;
use App\Modules\Notifications\DTO\Output\NotificationData;
use App\Modules\Notifications\DTO\Output\NotificationListData;
use App\Modules\Notifications\Exceptions\NotificationsException;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    public function list(User $user, ListNotificationsData $data): NotificationListData
    {
        $perPage = min(max($data->perPage, 1), 50);

        $paginator = $this->notifications->paginateForUser(
            $user,
            $data->unreadOnly,
            $perPage,
        );

        return NotificationListData::fromPaginator(
            $paginator,
            $this->notifications->unreadCountForUser($user),
        );
    }

    public function show(User $user, int $id): NotificationData
    {
        $notification = $this->notifications->findByIdForUser($id, $user)
            ?? throw NotificationsException::notFound();

        $this->notifications->markRead($notification);

        return NotificationData::fromModel($notification->refresh());
    }
}
