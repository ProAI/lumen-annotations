<?php

namespace ProAI\Annotations;

use Illuminate\Support\ServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app['config']['annotations.auto_scan']) {
            $this->scanRoutes();

            $this->scanEvents();
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

        $this->app->register('ProAI\Annotations\Providers\CommandsServiceProvider');
    }

    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->app->configure('annotations');
    }

    /**
     * Auto update routes.
     *
     * @return void
     */
    protected function scanRoutes()
    {
        $app = $this->app;

        // get classes
        $classes = $app['annotations.classfinder']->getClassesFromNamespace($app['config']['annotations.routes_namespace']);

        // build metadata
        $routes = $app['annotations.route.scanner']->scan($classes);

        // generate routes.php file for scanned routes
        $app['annotations.route.generator']->generate($routes);
    }

    /**
     * Auto update event bindings.
     *
     * @return void
     */
    protected function scanEvents()
    {
        $app = $this->app;

        // get classes
        $classes = $app['annotations.classfinder']->getClassesFromNamespace($app['config']['annotations.events_namespace']);

        // build metadata
        $events = $app['annotations.event.scanner']->scan($classes);

        // generate events.php file for scanned routes
        $app['annotations.event.generator']->generate($events);
    }
}
