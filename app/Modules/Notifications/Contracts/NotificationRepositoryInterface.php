<?php

namespace App\Modules\Notifications\Contracts;

use App\Models\User;
use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function findByIdForUser(int $id, User $user): ?UserNotification;

    public function paginateForUser(User $user, bool $unreadOnly, int $perPage): LengthAwarePaginator;

    public function unreadCountForUser(User $user): int;

    public function save(UserNotification $notification): UserNotification;

    public function markRead(UserNotification $notification): UserNotification;
}
