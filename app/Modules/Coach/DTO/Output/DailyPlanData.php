<?php

namespace App\Modules\Coach\DTO\Output;

use App\Modules\Coach\Enums\DailyPlanStatus;
use App\Modules\Coach\Enums\PlanMode;
use App\Modules\Coach\Enums\PlanSource;
use App\Modules\Coach\Models\CoachDailyPlan;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class DailyPlanData implements RespondsAsArray
{
    /**
     * @param  list<array<string, mixed>>  $steps
     * @param  list<array<string, mixed>>  $reminders
     */
    public function __construct(
        public string $date,
        public PlanMode $mode,
        public PlanSource $source,
        public int $totalMinutes,
        public string $greeting,
        public array $steps,
        public array $reminders,
        public bool $cached,
        public DailyPlanStatus $status = DailyPlanStatus::Ready,
        public ?string $message = null,
        public ?DailyCheckInData $checkIn = null,
    ) {}

    public static function fromStored(CoachDailyPlan $stored, ?string $message = null, ?DailyCheckInData $checkIn = null, bool $useBase = false): self
    {
        $plan = ($useBase && is_array($stored->plan_base) && $stored->plan_base !== [])
            ? $stored->plan_base
            : $stored->plan;
        $status = $stored->status ?? DailyPlanStatus::Ready;

        return new self(
            date: $stored->plan_date->toDateString(),
            mode: $stored->mode,
            source: $stored->source,
            totalMinutes: (int) ($plan['total_minutes'] ?? 0),
            greeting: (string) ($plan['greeting'] ?? ''),
            steps: is_array($plan['steps'] ?? null) ? $plan['steps'] : [],
            reminders: is_array($plan['reminders'] ?? null) ? $plan['reminders'] : [],
            cached: true,
            status: $status,
            message: $message ?? ($status === DailyPlanStatus::Generating
                ? self::generatingMessageFromPlan($plan)
                : null),
            checkIn: $checkIn,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $steps
     * @param  list<array<string, mixed>>  $reminders
     */
    public static function fresh(
        string $date,
        PlanMode $mode,
        PlanSource $source,
        int $totalMinutes,
        string $greeting,
        array $steps,
        array $reminders,
        DailyPlanStatus $status = DailyPlanStatus::Ready,
        ?string $message = null,
        ?DailyCheckInData $checkIn = null,
    ): self {
        return new self(
            date: $date,
            mode: $mode,
            source: $source,
            totalMinutes: $totalMinutes,
            greeting: $greeting,
            steps: $steps,
            reminders: $reminders,
            cached: false,
            status: $status,
            message: $message,
            checkIn: $checkIn,
        );
    }

    public function withStatus(DailyPlanStatus $status, ?string $message = null, bool $cached = false): self
    {
        return new self(
            date: $this->date,
            mode: $this->mode,
            source: $this->source,
            totalMinutes: $this->totalMinutes,
            greeting: $this->greeting,
            steps: $this->steps,
            reminders: $this->reminders,
            cached: $cached,
            status: $status,
            message: $message,
            checkIn: $this->checkIn,
        );
    }

    public function withCheckIn(?DailyCheckInData $checkIn, array $steps): self
    {
        return new self(
            date: $this->date,
            mode: $this->mode,
            source: $this->source,
            totalMinutes: $this->totalMinutes,
            greeting: $this->greeting,
            steps: $steps,
            reminders: $this->reminders,
            cached: $this->cached,
            status: $this->status,
            message: $this->message,
            checkIn: $checkIn,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPlanPayload(): array
    {
        return [
            'date' => $this->date,
            'mode' => $this->mode->value,
            'source' => $this->source->value,
            'total_minutes' => $this->totalMinutes,
            'greeting' => $this->greeting,
            'steps' => $this->steps,
            'reminders' => $this->reminders,
        ];
    }

    public function toArray(): array
    {
        return [
            ...$this->toPlanPayload(),
            'cached' => $this->cached,
            'status' => $this->status->value,
            'message' => $this->message,
            'check_in' => $this->checkIn?->toArray(),
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private static function generatingMessageFromPlan(array $plan): ?string
    {
        return is_string($plan['generating_message'] ?? null) ? $plan['generating_message'] : null;
    }
}
