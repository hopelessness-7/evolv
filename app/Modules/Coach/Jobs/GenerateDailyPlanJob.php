<?php

namespace App\Modules\Coach\Jobs;

use App\Models\User;
use App\Modules\Coach\Contracts\DailyPlanRepositoryInterface;
use App\Modules\Coach\Enums\DailyPlanStatus;
use App\Modules\Coach\Enums\PlanSource;
use App\Modules\Coach\Events\DailyPlanReady;
use App\Modules\Coach\Models\CoachDailyPlan;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Coach\Services\DailyPlanGenerator;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDailyPlanJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 120;

    public function __construct(
        public readonly int $userId,
        public readonly string $planDate,
    ) {}

    public function uniqueId(): string
    {
        return $this->userId.':'.$this->planDate;
    }

    public function handle(
        OnboardingProfileReaderInterface $onboarding,
        DailyPlanGenerator $generator,
        DailyPlanRepositoryInterface $plans,
        CoachService $coach,
    ): void {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $planDate = CarbonImmutable::parse($this->planDate)->startOfDay();
        $context = $onboarding->readForCoach($user);
        $generated = $generator->generate($planDate->toDateString(), $user, $context);
        $basePayload = $generated->toPlanPayload();
        $adapted = $coach->applyCheckInToGeneratedPlan($user, $planDate->toDateString(), $generated);

        $stored = $plans->findForUserAndDate($user, $planDate) ?? new CoachDailyPlan([
            'user_id' => $user->id,
            'plan_date' => $planDate->toDateString(),
        ]);

        $stored->fill([
            'mode' => $adapted->mode,
            'source' => $adapted->source,
            'status' => DailyPlanStatus::Ready,
            'plan_base' => $basePayload,
            'plan' => $adapted->toPlanPayload(),
        ]);

        $plans->save($stored);

        if ($adapted->source === PlanSource::Llm) {
            DailyPlanReady::dispatch($user, $adapted->withStatus(DailyPlanStatus::Ready));
        }
    }
}
