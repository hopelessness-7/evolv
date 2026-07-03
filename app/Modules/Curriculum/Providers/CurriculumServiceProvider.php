<?php

namespace App\Modules\Curriculum\Providers;

use App\Modules\Curriculum\Contracts\CurriculumRouteReaderInterface;
use App\Modules\Curriculum\Contracts\GraphRepositoryInterface;
use App\Modules\Curriculum\Contracts\NodeRepositoryInterface;
use App\Modules\Curriculum\Repositories\GraphRepository;
use App\Modules\Curriculum\Repositories\NodeRepository;
use App\Modules\Curriculum\Services\CurriculumService;
use App\Modules\Curriculum\Services\EntryNodeSelector;
use App\Modules\Curriculum\Services\RouteExpander;
use App\Modules\Shared\Services\PrimaryTrackResolver;
use Illuminate\Support\ServiceProvider;

class CurriculumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PrimaryTrackResolver::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);
        $this->app->bind(GraphRepositoryInterface::class, GraphRepository::class);
        $this->app->bind(CurriculumRouteReaderInterface::class, CurriculumService::class);
        $this->app->bind(EntryNodeSelector::class);
        $this->app->bind(RouteExpander::class);
        $this->app->bind(CurriculumService::class);
    }
}
