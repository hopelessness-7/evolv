<?php

namespace App\Modules\Coach\Contracts;

use App\Models\User;
use App\Modules\Coach\Models\CoachDailyPlan;
use Carbon\CarbonInterface;

interface DailyPlanRepositoryInterface
{
    public function findForUserAndDate(User $user, CarbonInterface $date): ?CoachDailyPlan;

    public function save(CoachDailyPlan $plan): CoachDailyPlan;
}
