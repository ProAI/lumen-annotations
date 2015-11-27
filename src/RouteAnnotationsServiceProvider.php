<?php

namespace ProAI\RouteAnnotations;

use Illuminate\Support\ServiceProvider;

class RouteAnnotationsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->app->register('ProAI\RouteAnnotations\Providers\CommandsServiceProvider');
    }

    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->app->configure('route');
    }
}
