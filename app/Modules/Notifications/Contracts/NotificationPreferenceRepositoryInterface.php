<?php

namespace App\Modules\Notifications\Contracts;

use App\Models\User;
use App\Modules\Notifications\Models\NotificationPreference;

interface NotificationPreferenceRepositoryInterface
{
    public function firstOrCreate(User $user): NotificationPreference;

    public function save(NotificationPreference $preference): NotificationPreference;
}
