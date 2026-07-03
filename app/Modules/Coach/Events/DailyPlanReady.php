<?php

namespace App\Modules\Coach\Events;

use App\Models\User;
use App\Modules\Coach\DTO\Output\DailyPlanData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyPlanReady
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly DailyPlanData $plan,
    ) {}
}
