<?php

namespace dinkara\repobuilder;

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