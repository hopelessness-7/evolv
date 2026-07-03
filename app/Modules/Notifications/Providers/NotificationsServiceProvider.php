<?php

namespace App\Modules\Notifications\Providers;

use App\Modules\Coach\Events\DailyPlanReady;
use App\Modules\Notifications\Contracts\NotificationDispatcherInterface;
use App\Modules\Notifications\Contracts\NotificationPreferenceRepositoryInterface;
use App\Modules\Notifications\Contracts\NotificationRepositoryInterface;
use App\Modules\Notifications\Listeners\NotifyDailyPlanReady;
use App\Modules\Notifications\Repositories\NotificationPreferenceRepository;
use App\Modules\Notifications\Repositories\NotificationRepository;
use App\Modules\Notifications\Services\DailyPlanNotificationService;
use App\Modules\Notifications\Services\NotificationDispatcher;
use App\Modules\Notifications\Services\NotificationPreferenceService;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->singleton(NotificationPreferenceRepositoryInterface::class, NotificationPreferenceRepository::class);
        $this->app->singleton(NotificationDispatcherInterface::class, NotificationDispatcher::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(NotificationPreferenceService::class);
        $this->app->singleton(DailyPlanNotificationService::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'notifications');

        Event::listen(DailyPlanReady::class, NotifyDailyPlanReady::class);
    }
}
