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
        $app = $this->app;

        $this->registerConfig();

        $this->app->register('ProAI\RouteAnnotations\Providers\CommandsServiceProvider');

        if ($app['config']['route.auto_scan'])
            $this->autoUpdateRoutes();
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

    /**
     * Scan route annotations and update routes.
     *
     * @return void
     */
    public function autoUpdateRoutes()
    {
        $app = $this->app;

        // get classes
        $classes = $app['route.annotations.classfinder']->getClassesFromNamespace($app['config']['route.controllers_namespace']);

        // build metadata
        $routes = $app['route.annotations.scanner']->scan($classes);

        // generate routes.php file for scanned routes
        $app['route.annotations.generator']->generate($routes);
    }
}
