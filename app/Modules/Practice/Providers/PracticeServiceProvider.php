<?php

namespace App\Modules\Practice\Providers;

use App\Modules\Practice\Contracts\AttemptRepositoryInterface;
use App\Modules\Practice\Contracts\CodeExecutionDriverInterface;
use App\Modules\Practice\Contracts\PracticeExerciseReaderInterface;
use App\Modules\Practice\Contracts\UserSkillRepositoryInterface;
use App\Modules\Practice\Drivers\Judge0Driver;
use App\Modules\Practice\Repositories\AttemptRepository;
use App\Modules\Practice\Repositories\UserSkillRepository;
use App\Modules\Practice\Services\ExerciseResolver;
use App\Modules\Practice\Services\ExerciseRunner;
use App\Modules\Practice\Services\PracticeService;
use App\Modules\Practice\Services\UserSkillUpdater;
use Illuminate\Support\ServiceProvider;

class PracticeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CodeExecutionDriverInterface::class, Judge0Driver::class);
        $this->app->singleton(AttemptRepositoryInterface::class, AttemptRepository::class);
        $this->app->singleton(UserSkillRepositoryInterface::class, UserSkillRepository::class);
        $this->app->singleton(PracticeExerciseReaderInterface::class, ExerciseResolver::class);
        $this->app->singleton(ExerciseResolver::class);
        $this->app->singleton(ExerciseRunner::class);
        $this->app->singleton(UserSkillUpdater::class);
        $this->app->singleton(PracticeService::class);
    }
}
