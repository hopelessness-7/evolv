<?php

namespace App\Modules\Coach\Repositories;

use App\Models\User;
use App\Modules\Coach\Contracts\DailyCheckInRepositoryInterface;
use App\Modules\Coach\Models\DailyCheckIn;
use Carbon\CarbonInterface;

class DailyCheckInRepository implements DailyCheckInRepositoryInterface
{
    public function findForUserAndDate(User $user, CarbonInterface $planDate): ?DailyCheckIn
    {
        return DailyCheckIn::query()
            ->where('user_id', $user->id)
            ->whereDate('plan_date', $planDate->toDateString())
            ->first();
    }

    public function save(DailyCheckIn $checkIn): DailyCheckIn
    {
        $checkIn->save();

        return $checkIn->refresh();
    }
}
