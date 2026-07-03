<?php

namespace App\Modules\Learn\Providers;

use App\Modules\Learn\Services\LearnService;
use Illuminate\Support\ServiceProvider;

class LearnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LearnService::class);
    }
}
