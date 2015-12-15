<?php

namespace ProAI\Annotations;

use Illuminate\Support\ServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider
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

        $this->app->register('ProAI\Annotations\Providers\CommandsServiceProvider');

        if ($app['config']['annotations.auto_scan'])
            $this->registerAutoScanAnnotations();
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
     * Scan annotations and update routes and event bindings.
     *
     * @return void
     */
    public function registerAutoScanAnnotations()
    {
        $app = $this->app;

        // get classes
        $classes = $app['annotations.classfinder']->getClassesFromNamespace($app['config']['annotations.routes_namespace']);

        // build metadata
        $routes = $app['annotations.scanner']->scan($classes);

        // generate routes.php file for scanned routes
        $app['annotations.generator']->generate($routes);
    }
}
