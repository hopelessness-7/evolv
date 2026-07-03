<?php

namespace App\Modules\Onboarding\Contracts;

use App\Models\User;
use App\Modules\Onboarding\Enums\AnalyticsEvent;
use App\Modules\Onboarding\Models\OnboardingSession;

interface AnalyticsRecorderInterface
{
    /** @param  array<string, mixed>  $properties */
    public function record(AnalyticsEvent $event, User $user, ?OnboardingSession $session = null, array $properties = []): void;
}
