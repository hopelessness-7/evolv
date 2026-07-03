<?php

namespace App\Modules\Content\Providers;

use App\Modules\Content\Contracts\ContentVersionRepositoryInterface;
use App\Modules\Content\Repositories\ContentVersionRepository;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentVersionRepositoryInterface::class, ContentVersionRepository::class);
    }
}
