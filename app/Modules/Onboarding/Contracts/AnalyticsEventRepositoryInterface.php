<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\Enums\AnalyticsEvent;

interface AnalyticsEventRepositoryInterface
{
    /** @param  array<string, mixed>  $properties */
    public function record(AnalyticsEvent $event, User $user, ?int $sessionId = null, array $properties = []): void;
}
