<?php

namespace App\Modules\Onboarding\Services;

use App\Models\User;
use App\Modules\Onboarding\Contracts\AnalyticsEventRepositoryInterface;
use App\Modules\Onboarding\Contracts\AnalyticsRecorderInterface;
use App\Modules\Onboarding\Enums\AnalyticsEvent;
use App\Modules\Onboarding\Models\OnboardingSession;

class AnalyticsRecorder implements AnalyticsRecorderInterface
{
    public function __construct(private readonly AnalyticsEventRepositoryInterface $events) {}

    public function record(AnalyticsEvent $event, User $user, ?OnboardingSession $session = null, array $properties = []): void
    {
        $this->events->record($event, $user, $session?->id, $properties);
    }
}
