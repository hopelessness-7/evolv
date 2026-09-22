<?php

namespace App\Modules\Gamification\Providers;

use App\Modules\Content\Events\QuizAnswered;
use App\Modules\Gamification\Contracts\GamificationProfileRepositoryInterface;
use App\Modules\Gamification\Contracts\GamificationReaderInterface;
use App\Modules\Gamification\Contracts\GamificationRecorderInterface;
use App\Modules\Gamification\Contracts\XpLedgerRepositoryInterface;
use App\Modules\Gamification\Listeners\OnAttemptAccepted;
use App\Modules\Gamification\Listeners\OnQuizAnswered;
use App\Modules\Gamification\Listeners\OnStepCompleted;
use App\Modules\Gamification\Repositories\GamificationProfileRepository;
use App\Modules\Gamification\Repositories\XpLedgerRepository;
use App\Modules\Gamification\Services\GamificationService;
use App\Modules\LearningPath\Events\StepCompleted;
use App\Modules\Practice\Events\AttemptAccepted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class GamificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GamificationProfileRepositoryInterface::class, GamificationProfileRepository::class);
        $this->app->singleton(XpLedgerRepositoryInterface::class, XpLedgerRepository::class);
        $this->app->singleton(GamificationService::class);
        $this->app->singleton(GamificationReaderInterface::class, fn ($app) => $app->make(GamificationService::class));
        $this->app->singleton(GamificationRecorderInterface::class, fn ($app) => $app->make(GamificationService::class));
    }

    public function boot(): void
    {
        Event::listen(StepCompleted::class, OnStepCompleted::class);
        Event::listen(QuizAnswered::class, OnQuizAnswered::class);
        Event::listen(AttemptAccepted::class, OnAttemptAccepted::class);
    }
}
