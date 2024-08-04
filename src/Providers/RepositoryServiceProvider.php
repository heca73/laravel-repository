<?php

namespace Heca73\LaravelRepository\Providers;

use Heca73\LaravelRepository\Commands\CreateRepositoryCommand;
use Heca73\LaravelRepository\Repository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('heca73-laravel-repository', function ($app) {
            return new Repository();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            CreateRepositoryCommand::class
        ]);
    }
}