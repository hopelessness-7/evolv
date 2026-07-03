<?php

namespace App\Modules\LearningPath\Providers;

use App\Modules\LearningPath\Contracts\LearningPathReaderInterface;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\Repositories\LearningPlanRepository;
use App\Modules\LearningPath\Services\LearningPathService;
use Illuminate\Support\ServiceProvider;

class LearningPathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LearningPlanRepositoryInterface::class, LearningPlanRepository::class);
        $this->app->singleton(LearningPathService::class);
        $this->app->singleton(LearningPathReaderInterface::class, LearningPathService::class);
    }
}
