<?php

namespace App\Modules\Onboarding\Repositories;

use App\Models\User;
use App\Modules\Onboarding\Contracts\AnalyticsEventRepositoryInterface;
use App\Modules\Onboarding\Enums\AnalyticsEvent;
use App\Modules\Onboarding\Models\AnalyticsEvent as AnalyticsEventModel;

class AnalyticsEventRepository implements AnalyticsEventRepositoryInterface
{
    public function record(AnalyticsEvent $event, User $user, ?int $sessionId = null, array $properties = []): void
    {
        AnalyticsEventModel::query()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'event' => $event->value,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
