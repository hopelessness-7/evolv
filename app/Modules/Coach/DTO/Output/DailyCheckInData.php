<?php

namespace App\Modules\Coach\DTO\Output;

use App\Modules\Coach\Models\DailyCheckIn;
use App\Modules\Shared\Contracts\RespondsAsArray;

final readonly class DailyCheckInData implements RespondsAsArray
{
    public function __construct(
        public int $id,
        public string $planDate,
        public int $energy,
        public int $focus,
        public int $practiceReady,
        public ?string $note,
        public float $loadFactor,
        public string $loadBand,
    ) {}

    public static function fromModel(DailyCheckIn $model): self
    {
        $avg = ($model->energy + $model->focus + $model->practice_ready) / 3;
        [$factor, $band] = self::factorAndBand($avg);

        return new self(
            id: (int) $model->id,
            planDate: $model->plan_date->toDateString(),
            energy: (int) $model->energy,
            focus: (int) $model->focus,
            practiceReady: (int) $model->practice_ready,
            note: $model->note,
            loadFactor: $factor,
            loadBand: $band,
        );
    }

    /**
     * @return array{0: float, 1: string}
     */
    public static function factorAndBand(float $average): array
    {
        if ($average <= 2.33) {
            return [0.5, 'low'];
        }

        if ($average <= 3.5) {
            return [0.75, 'medium'];
        }

        return [1.0, 'high'];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'plan_date' => $this->planDate,
            'energy' => $this->energy,
            'focus' => $this->focus,
            'practice_ready' => $this->practiceReady,
            'note' => $this->note,
            'load_factor' => $this->loadFactor,
            'load_band' => $this->loadBand,
        ];
    }
}
