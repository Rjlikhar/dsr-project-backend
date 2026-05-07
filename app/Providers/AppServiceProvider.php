<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\DsrService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\DsrServiceInterface;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(DsrServiceInterface::class, DsrService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
