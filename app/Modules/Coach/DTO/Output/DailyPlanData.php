<?php

namespace App\Modules\Coach\DTO\Output;

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
    ) {}

    public static function fromStored(CoachDailyPlan $stored): self
    {
        $plan = $stored->plan;

        return new self(
            date: $stored->plan_date->toDateString(),
            mode: $stored->mode,
            source: $stored->source,
            totalMinutes: (int) ($plan['total_minutes'] ?? 0),
            greeting: (string) ($plan['greeting'] ?? ''),
            steps: is_array($plan['steps'] ?? null) ? $plan['steps'] : [],
            reminders: is_array($plan['reminders'] ?? null) ? $plan['reminders'] : [],
            cached: true,
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
        );
    }

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
        ];
    }
}
