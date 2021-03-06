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
            dirname(__DIR__).'/src/Support/Lang/en' => resource_path('lang/en'),
            dirname(__DIR__).'/src/Config' => config_path(),
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