<?php

namespace Heca73\LaravelRepository\Providers;

use Heca73\LaravelRepository\Commands\CreateRepositoryCommand;
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