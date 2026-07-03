<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Coach\Events\DailyPlanReady;
use App\Modules\Notifications\Services\DailyPlanNotificationService;

class NotifyDailyPlanReady
{
    public function __construct(
        private readonly DailyPlanNotificationService $notifier,
    ) {}

    public function handle(DailyPlanReady $event): void
    {
        $this->notifier->notifyFromPlan($event->user, $event->plan);
    }
}
