<?php

namespace App\Modules\Coach\DTO\Input;

final readonly class StoreDailyCheckInData
{
    public function __construct(
        public int $energy,
        public int $focus,
        public int $practiceReady,
        public ?string $planDate = null,
        public ?string $note = null,
    ) {}
}
