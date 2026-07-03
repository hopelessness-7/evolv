<?php

namespace App\Modules\Notifications\Enums;

enum NotificationType: string
{
    case DailyPlan = 'daily_plan';
    case OnboardingReminder = 'onboarding_reminder';
    case CoachTip = 'coach_tip';
    case System = 'system';
}
