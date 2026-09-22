<?php

namespace App\Modules\Coach\Services;

use App\Models\User;
use App\Modules\Coach\Contracts\DailyCheckInRepositoryInterface;
use App\Modules\Coach\Contracts\DailyPlanRepositoryInterface;
use App\Modules\Coach\DTO\Input\GetDailyPlanData;
use App\Modules\Coach\DTO\Input\StoreDailyCheckInData;
use App\Modules\Coach\DTO\Output\DailyCheckInData;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use App\Modules\Coach\DTO\Output\StoreDailyCheckInResultData;
use App\Modules\Coach\Enums\DailyPlanStatus;
use App\Modules\Coach\Enums\PlanStepType;
use App\Modules\Coach\Exceptions\CoachException;
use App\Modules\Coach\Jobs\GenerateDailyPlanJob;
use App\Modules\Coach\Models\CoachDailyPlan;
use App\Modules\Coach\Models\DailyCheckIn;
use App\Modules\Onboarding\Contracts\OnboardingProfileReaderInterface;
use App\Modules\Shared\Support\InterfaceLanguage;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

class CoachService
{
    public function __construct(
        private readonly OnboardingProfileReaderInterface $onboarding,
        private readonly DailyPlanRepositoryInterface $plans,
        private readonly DailyCheckInRepositoryInterface $checkIns,
        private readonly FallbackDailyPlanBuilder $fallbackBuilder,
        private readonly CheckInStepBuilder $checkInStepBuilder,
        private readonly PlanLoadAdapter $loadAdapter,
    ) {}

    public function getDailyPlan(User $user, GetDailyPlanData $data): DailyPlanData
    {
        $context = $this->onboarding->readForCoach($user);
        $timezone = (string) ($context->profileSummary['timezone'] ?? 'UTC');
        $lang = InterfaceLanguage::fromProfileSummary($context->profileSummary);
        $planDate = $this->resolvePlanDate($data->date, $timezone);
        $existing = $this->plans->findForUserAndDate($user, $planDate);

        if ($existing !== null && ! $data->refresh) {
            $plan = DailyPlanData::fromStored(
                $existing,
                $existing->status === DailyPlanStatus::Generating
                    ? $this->generatingMessage($lang)
                    : null,
            );

            return $this->decorateWithCheckIn($plan, $user, $planDate, $lang);
        }

        $message = $this->generatingMessage($lang);

        if ($existing === null) {
            $fallback = $this->fallbackBuilder->build($planDate->toDateString(), $user, $context);
            $payload = $fallback->toPlanPayload();
            $stored = new CoachDailyPlan([
                'user_id' => $user->id,
                'plan_date' => $planDate->toDateString(),
                'mode' => $fallback->mode,
                'source' => $fallback->source,
                'status' => DailyPlanStatus::Generating,
                'plan' => $payload,
                'plan_base' => $payload,
            ]);
            $this->plans->save($stored);
            $this->dispatchGeneration($user->id, $planDate->toDateString());

            return $this->decorateWithCheckIn(
                $fallback->withStatus(DailyPlanStatus::Generating, $message, cached: false),
                $user,
                $planDate,
                $lang,
            );
        }

        $existing->status = DailyPlanStatus::Generating;
        $this->plans->save($existing);
        $this->dispatchGeneration($user->id, $planDate->toDateString());

        return $this->decorateWithCheckIn(
            DailyPlanData::fromStored($existing, $message),
            $user,
            $planDate,
            $lang,
        );
    }

    public function storeCheckIn(User $user, StoreDailyCheckInData $data): StoreDailyCheckInResultData
    {
        $context = $this->onboarding->readForCoach($user);
        $timezone = (string) ($context->profileSummary['timezone'] ?? 'UTC');
        $lang = InterfaceLanguage::fromProfileSummary($context->profileSummary);
        $planDate = $this->resolvePlanDate($data->planDate, $timezone);

        $checkIn = $this->checkIns->findForUserAndDate($user, $planDate) ?? new DailyCheckIn([
            'user_id' => $user->id,
            'plan_date' => $planDate->toDateString(),
        ]);

        $checkIn->fill([
            'energy' => $data->energy,
            'focus' => $data->focus,
            'practice_ready' => $data->practiceReady,
            'note' => $data->note,
        ]);
        $this->checkIns->save($checkIn);
        $checkInData = DailyCheckInData::fromModel($checkIn);

        $stored = $this->plans->findForUserAndDate($user, $planDate);
        if ($stored === null) {
            $fallback = $this->fallbackBuilder->build($planDate->toDateString(), $user, $context);
            $payload = $fallback->toPlanPayload();
            $stored = new CoachDailyPlan([
                'user_id' => $user->id,
                'plan_date' => $planDate->toDateString(),
                'mode' => $fallback->mode,
                'source' => $fallback->source,
                'status' => DailyPlanStatus::Ready,
                'plan' => $payload,
                'plan_base' => $payload,
            ]);
            $this->plans->save($stored);
        } elseif (! is_array($stored->plan_base) || $stored->plan_base === []) {
            $stored->plan_base = $stored->plan;
            $this->plans->save($stored);
        }

        $basePlan = DailyPlanData::fromStored($stored, useBase: true);
        $adapted = $this->loadAdapter->apply($basePlan, $checkInData, $lang);

        $stored->plan = $adapted->toPlanPayload();
        $this->plans->save($stored);

        $responsePlan = DailyPlanData::fromStored($stored)->withCheckIn($checkInData, $adapted->steps);

        return new StoreDailyCheckInResultData($checkInData, $responsePlan);
    }

    public function getCheckIn(User $user, ?string $date): ?DailyCheckInData
    {
        $context = $this->onboarding->readForCoach($user);
        $timezone = (string) ($context->profileSummary['timezone'] ?? 'UTC');
        $planDate = $this->resolvePlanDate($date, $timezone);
        $model = $this->checkIns->findForUserAndDate($user, $planDate);

        return $model !== null ? DailyCheckInData::fromModel($model) : null;
    }

    /**
     * Re-apply check-in load after LLM (or fallback) regeneration.
     */
    public function applyCheckInToGeneratedPlan(User $user, string $planDate, DailyPlanData $plan): DailyPlanData
    {
        $context = $this->onboarding->readForCoach($user);
        $lang = InterfaceLanguage::fromProfileSummary($context->profileSummary);
        $date = CarbonImmutable::parse($planDate)->startOfDay();
        $model = $this->checkIns->findForUserAndDate($user, $date);

        if ($model === null) {
            return $plan;
        }

        return $this->loadAdapter->apply($plan, DailyCheckInData::fromModel($model), $lang);
    }

    private function decorateWithCheckIn(
        DailyPlanData $plan,
        User $user,
        CarbonImmutable $planDate,
        string $lang,
    ): DailyPlanData {
        $model = $this->checkIns->findForUserAndDate($user, $planDate);

        if ($model !== null) {
            $checkIn = DailyCheckInData::fromModel($model);
            $steps = array_values(array_filter(
                $plan->steps,
                fn (array $step): bool => ($step['type'] ?? null) !== PlanStepType::CheckIn->value,
            ));

            return $plan->withCheckIn($checkIn, $steps);
        }

        $steps = array_values(array_filter(
            $plan->steps,
            fn (array $step): bool => ($step['type'] ?? null) !== PlanStepType::CheckIn->value,
        ));
        array_unshift($steps, $this->checkInStepBuilder->build($lang));

        return $plan->withCheckIn(null, $steps);
    }

    private function dispatchGeneration(int $userId, string $planDate): void
    {
        GenerateDailyPlanJob::dispatch($userId, $planDate);
    }

    private function generatingMessage(string $lang): string
    {
        return InterfaceLanguage::pick($lang, [
            'plan_generating' => [
                'ru' => 'План готовится. Обновите через несколько секунд.',
                'en' => 'Your plan is being prepared. Refresh in a few seconds.',
            ],
        ], 'plan_generating');
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
