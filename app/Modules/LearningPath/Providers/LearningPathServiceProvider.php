<?php

namespace App\Modules\LearningPath\Providers;

use App\Modules\Content\Events\QuizAnswered;
use App\Modules\LearningPath\Contracts\LearningPathReaderInterface;
use App\Modules\LearningPath\Contracts\LearningPlanRepositoryInterface;
use App\Modules\LearningPath\Repositories\LearningPlanRepository;
use App\Modules\LearningPath\Services\LearningPathService;
use App\Modules\LearningPath\Services\LessonStepAutoCompletion;
use App\Modules\Practice\Events\AttemptAccepted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LearningPathServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LearningPlanRepositoryInterface::class, LearningPlanRepository::class);
        $this->app->singleton(LearningPathService::class);
        $this->app->singleton(LearningPathReaderInterface::class, LearningPathService::class);
        $this->app->singleton(LessonStepAutoCompletion::class);
    }

    public function boot(): void
    {
        Event::listen(
            QuizAnswered::class,
            [LessonStepAutoCompletion::class, 'handleQuizAnswered'],
        );
        Event::listen(
            AttemptAccepted::class,
            [LessonStepAutoCompletion::class, 'handleAttemptAccepted'],
        );
    }
}
