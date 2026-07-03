<?php

namespace App\Modules\Coach\Providers;

use App\Modules\Coach\Contracts\DailyPlanRepositoryInterface;
use App\Modules\Coach\Repositories\DailyPlanRepository;
use App\Modules\Coach\Services\CoachService;
use App\Modules\Coach\Services\DailyPlanGenerator;
use App\Modules\Coach\Services\FallbackDailyPlanBuilder;
use Illuminate\Support\ServiceProvider;

class CoachServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DailyPlanRepositoryInterface::class, DailyPlanRepository::class);
        $this->app->bind(FallbackDailyPlanBuilder::class);
        $this->app->bind(DailyPlanGenerator::class);
        $this->app->bind(CoachService::class);
    }
}
