<?php

namespace App\Modules\Coach\Contracts;

use App\Models\User;
use App\Modules\Coach\Models\DailyCheckIn;
use Carbon\CarbonInterface;

interface DailyCheckInRepositoryInterface
{
    public function findForUserAndDate(User $user, CarbonInterface $planDate): ?DailyCheckIn;

    public function save(DailyCheckIn $checkIn): DailyCheckIn;
}
