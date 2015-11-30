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

        if ($this->app->config('route.annotations.auto_scan'))
            $this->autoUpdateDatabase();
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
    public function updateRoutes()
    {
        $app = app();

        // get classes
        $classes = $app['route.annotations.classfinder']->getClassesFromNamespace(config('route.controllers_namespace'));

        // build metadata
        $routes = $app['route.annotations.scanner']->scan($classes);

        // generate routes.php file for scanned routes
        $app['route.annotations.generator']->generate($routes);
    }
}
