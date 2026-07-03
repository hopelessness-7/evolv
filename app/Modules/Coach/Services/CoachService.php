<?php

namespace App\Modules\Coach\Services;

use App\Models\User;
use App\Modules\Coach\Contracts\DailyPlanRepositoryInterface;
use App\Modules\Coach\DTO\Input\GetDailyPlanData;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\Events\DailyPlanReady;
use App\Modules\Coach\Exceptions\CoachException;
use App\Modules\Coach\Models\CoachDailyPlan;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

class CoachService
{
    public function __construct(
        private readonly OnboardingProfileReaderInterface $onboarding,
        private readonly DailyPlanRepositoryInterface $plans,
        private readonly DailyPlanGenerator $generator,
    ) {}

    public function getDailyPlan(User $user, GetDailyPlanData $data): DailyPlanData
    {
        $context = $this->onboarding->readForCoach($user);
        $timezone = (string) ($context->profileSummary['timezone'] ?? 'UTC');
        $planDate = $this->resolvePlanDate($data->date, $timezone);

        if (! $data->refresh) {
            $cached = $this->plans->findForUserAndDate($user, $planDate);

            if ($cached !== null) {
                return DailyPlanData::fromStored($cached);
            }
        }

        $plan = $this->generator->generate($planDate->toDateString(), $user, $context);

        $stored = new CoachDailyPlan([
            'user_id' => $user->id,
            'plan_date' => $planDate->toDateString(),
            'mode' => $plan->mode,
            'source' => $plan->source,
            'plan' => $plan->toPlanPayload(),
        ]);

        $existing = $this->plans->findForUserAndDate($user, $planDate);

        if ($existing !== null) {
            $stored = $existing;
            $stored->fill([
                'mode' => $plan->mode,
                'source' => $plan->source,
                'plan' => $plan->toPlanPayload(),
            ]);
        }

        $this->plans->save($stored);

        if (! $plan->cached) {
            DailyPlanReady::dispatch($user, $plan);
        }

        return $plan;
    }

    private function resolvePlanDate(?string $date, string $timezone): CarbonImmutable
    {
        try {
            if ($date !== null && $date !== '') {
                return CarbonImmutable::parse($date, $timezone)->startOfDay();
            }
        } catch (InvalidFormatException) {
            throw CoachException::invalidPlanDate((string) $date);
        }

        return CarbonImmutable::now($timezone)->startOfDay();
    }
}
