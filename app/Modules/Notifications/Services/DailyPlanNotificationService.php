<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Notifications\Contracts\NotificationDispatcherInterface;
use App\Modules\Notifications\DTO\Input\SendNotificationData;
use App\Modules\Notifications\Enums\NotificationType;

class DailyPlanNotificationService
{
    public function __construct(
        private readonly NotificationDispatcherInterface $dispatcher,
    ) {}

    public function notifyFromPlan(User $user, DailyPlanData $plan): void
    {
        if ($plan->cached) {
            return;
        }

        $this->dispatcher->send($user, new SendNotificationData(
            type: NotificationType::DailyPlan,
            title: 'План на '.$plan->date,
            body: $this->buildPlanBody($plan),
            data: [
                'date' => $plan->date,
                'mode' => $plan->mode->value,
                'total_minutes' => $plan->totalMinutes,
                'steps_count' => count($plan->steps),
            ],
        ));

        foreach ($plan->reminders as $reminder) {
            if (! is_array($reminder)) {
                continue;
            }

            $message = (string) ($reminder['message'] ?? '');

            if ($message === '') {
                continue;
            }

            $this->dispatcher->send($user, new SendNotificationData(
                type: NotificationType::OnboardingReminder,
                title: 'Онбординг: '.((string) ($reminder['questionnaire_code'] ?? 'шаг')),
                body: $message,
                data: $reminder,
            ));
        }
    }

    private function buildPlanBody(DailyPlanData $plan): string
    {
        $lines = [$plan->greeting];

        foreach (array_slice($plan->steps, 0, 3) as $step) {
            if (! is_array($step)) {
                continue;
            }

            $title = (string) ($step['title'] ?? '');
            $minutes = (int) ($step['minutes'] ?? 0);

            if ($title !== '') {
                $lines[] = $minutes > 0
                    ? "• {$title} ({$minutes} мин)"
                    : "• {$title}";
            }
        }

        return implode("\n", $lines);
    }
}
