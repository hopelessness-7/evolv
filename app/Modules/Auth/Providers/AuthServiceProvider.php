<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Contracts\AccessTokenRepositoryInterface;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use App\Modules\Auth\Repositories\AccessTokenRepository;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(AccessTokenRepositoryInterface::class, AccessTokenRepository::class);
        $this->app->singleton(AuthService::class);
    }
}
