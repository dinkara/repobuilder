<?php

namespace Dinkara\RepoBuilder;

use Illuminate\Support\ServiceProvider;
use Dinkara\RepoBuilder\Console\RepositoryBuilder;

class RepositoryBuilderServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RepositoryBuilder::class,
            ]);
        }

        $this->publishes([
            dirname(__DIR__).'/Support/Lang/en' => resource_path('lang/en'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}