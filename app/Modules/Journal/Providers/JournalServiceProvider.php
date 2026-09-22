<?php

namespace App\Modules\Journal\Providers;

use App\Modules\Journal\Contracts\JournalEntryRepositoryInterface;
use App\Modules\Journal\Repositories\JournalEntryRepository;
use App\Modules\Journal\Services\JournalService;
use Illuminate\Support\ServiceProvider;

class JournalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JournalEntryRepositoryInterface::class, JournalEntryRepository::class);
        $this->app->singleton(JournalService::class);
    }
}
