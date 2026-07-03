<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Contracts\NotificationPreferenceRepositoryInterface;
use App\Modules\Notifications\DTO\Input\UpdateNotificationPreferencesData;
use App\Modules\Notifications\DTO\Output\NotificationPreferenceData;

class NotificationPreferenceService
{
    public function __construct(
        private readonly NotificationPreferenceRepositoryInterface $preferences,
    ) {}

    public function get(User $user): NotificationPreferenceData
    {
        return NotificationPreferenceData::fromModel(
            $this->preferences->firstOrCreate($user),
        );
    }

    public function update(User $user, UpdateNotificationPreferencesData $data): NotificationPreferenceData
    {
        $preference = $this->preferences->firstOrCreate($user);
        $preference->email_enabled = $data->emailEnabled;
        $this->preferences->save($preference);

        return NotificationPreferenceData::fromModel($preference);
    }
}
