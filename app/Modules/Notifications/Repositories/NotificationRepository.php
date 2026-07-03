<?php

namespace App\Modules\Notifications\Repositories;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationRepositoryInterface;
use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function findByIdForUser(int $id, User $user): ?UserNotification
    {
        return UserNotification::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->first();
    }

    public function paginateForUser(User $user, bool $unreadOnly, int $perPage): LengthAwarePaginator
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function unreadCountForUser(User $user): int
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function save(UserNotification $notification): UserNotification
    {
        $notification->save();

        return $notification;
    }

    public function markRead(UserNotification $notification): UserNotification
    {
        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return $notification;
    }
}
