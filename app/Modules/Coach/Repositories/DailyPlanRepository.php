<?php

namespace App\Modules\Coach\Repositories;

use App\Models\User;
use App\Modules\Coach\Contracts\DailyPlanRepositoryInterface;
use App\Modules\Coach\Models\CoachDailyPlan;
use Carbon\CarbonInterface;

class DailyPlanRepository implements DailyPlanRepositoryInterface
{
    public function findForUserAndDate(User $user, CarbonInterface $date): ?CoachDailyPlan
    {
        return CoachDailyPlan::query()
            ->where('user_id', $user->id)
            ->whereDate('plan_date', $date->toDateString())
            ->first();
    }

    public function save(CoachDailyPlan $plan): CoachDailyPlan
    {
        $plan->save();

        return $plan;
    }
}
