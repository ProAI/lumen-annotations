<?php

namespace ProAI\RouteAnnotations;

use Illuminate\Support\ServiceProvider;

class RouteAnnotationsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $app = $this->app;
        
        if(! empty($app['config']['route.annotations.dev_auto_scan'])) {

            // todo: scan all routes
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->app->register('ProAI\Datamapper\Presenter\Providers\CommandsServiceProvider');
    }

    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $configPath = __DIR__ . '/../config/route-annotations.php';

        $this->mergeConfigFrom($configPath, 'route.annotations');

        $this->publishes([$configPath => config_path('route-annotations.php')], 'route.annotations');
    }
}
