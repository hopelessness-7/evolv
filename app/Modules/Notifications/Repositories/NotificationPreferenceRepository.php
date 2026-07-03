<?php

namespace App\Modules\Notifications\Repositories;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationPreferenceRepositoryInterface;
use App\Modules\Notifications\Models\NotificationPreference;

class NotificationPreferenceRepository implements NotificationPreferenceRepositoryInterface
{
    public function firstOrCreate(User $user): NotificationPreference
    {
        return NotificationPreference::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['email_enabled' => true],
        );
    }

    public function save(NotificationPreference $preference): NotificationPreference
    {
        $preference->save();

        return $preference;
    }
}
