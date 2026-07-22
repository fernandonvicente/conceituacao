<?php

namespace App\Providers;

use App\Repositories\ProfileEloquentORM;
use App\Repositories\ProfileRepositoryInterface;
use App\Repositories\UserEloquentORM;
use App\Repositories\UserProfileEloquentORM;
use App\Repositories\UserProfileRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserEloquentORM::class,
        );

        $this->app->bind(
            ProfileRepositoryInterface::class,
            ProfileEloquentORM::class,
        );

        $this->app->bind(
            UserProfileRepositoryInterface::class,
            UserProfileEloquentORM::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
