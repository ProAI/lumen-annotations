<?php

namespace ProAI\Annotations\Providers;

use Illuminate\Support\ServiceProvider;
use ProAI\Annotations\Metadata\RouteScanner;
use ProAI\Annotations\Routing\Generator as RouteGenerator;
use ProAI\Annotations\Metadata\EventScanner;
use ProAI\Annotations\Events\Generator as EventGenerator;
use ProAI\Annotations\Console\RouteScanCommand;
use ProAI\Annotations\Console\RouteClearCommand;
use ProAI\Annotations\Console\EventScanCommand;
use ProAI\Annotations\Console\EventClearCommand;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register('ProAI\Annotations\Providers\MetadataServiceProvider');

        $this->registerRouteScanner();

        $this->registerRouteGenerator();

        $this->registerEventScanner();

        $this->registerEventGenerator();

        $this->registerCommands();
    }

    /**
     * Register the route scanner implementation.
     *
     * @return void
     */
    protected function registerRouteScanner()
    {
        $this->app->singleton('annotations.route.scanner', function ($app) {
            $reader = $app['annotations.annotationreader'];

            return new RouteScanner($reader);
        });
    }

    /**
     * Register the route generator implementation.
     *
     * @return void
     */
    protected function registerRouteGenerator()
    {
        $app = $this->app;

        $app->singleton('annotations.route.generator', function ($app) {
            $path = storage_path('framework');

            return new RouteGenerator($app['files'], $path, 'routes.php');
        });
    }

    /**
     * Register the event scanner implementation.
     *
     * @return void
     */
    protected function registerEventScanner()
    {
        $this->app->singleton('annotations.event.scanner', function ($app) {
            $reader = $app['annotations.annotationreader'];

            return new EventScanner($reader, $app['config']['annotations']);
        });
    }

    /**
     * Register the event generator implementation.
     *
     * @return void
     */
    protected function registerEventGenerator()
    {
        $app = $this->app;

        $app->singleton('annotations.event.generator', function ($app) {
            $path = storage_path('framework');

            return new EventGenerator($app['files'], $path, 'events.php');
        });
    }

    /**
     * Register all of the migration commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        // create singletons of each command
        $commands = array('RouteScan', 'RouteClear', 'EventScan', 'EventClear');

        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        // register commands
        $this->commands(
            'command.route.scan',
            'command.route.clear',
            'command.event.scan',
            'command.event.clear'
        );
    }

    /**
     * Register the "route:scan" command.
     *
     * @return void
     */
    protected function registerRouteScanCommand()
    {
        $this->app->singleton('command.route.scan', function ($app) {
            return new RouteScanCommand(
                $app['annotations.classfinder'],
                $app['annotations.route.scanner'],
                $app['annotations.route.generator'],
                $app['config']['annotations']
            );
        });
    }

    /**
     * Register the "route:clear" command.
     *
     * @return void
     */
    protected function registerRouteClearCommand()
    {
        $this->app->singleton('command.route.clear', function ($app) {
            return new RouteClearCommand(
                $app['annotations.route.generator']
            );
        });
    }

    /**
     * Register the "event:scan" command.
     *
     * @return void
     */
    protected function registerEventScanCommand()
    {
        $this->app->singleton('command.event.scan', function ($app) {
            return new EventScanCommand(
                $app['annotations.classfinder'],
                $app['annotations.event.scanner'],
                $app['annotations.event.generator'],
                $app['config']['annotations']
            );
        });
    }

    /**
     * Register the "event:clear" command.
     *
     * @return void
     */
    protected function registerEventClearCommand()
    {
        $this->app->singleton('command.event.clear', function ($app) {
            return new EventClearCommand(
                $app['annotations.event.generator']
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'annotations.route.scanner',
            'annotations.route.generator',
            'annotations.event.scanner',
            'annotations.event.generator',
            'command.route.scan',
            'command.route.clear',
            'command.event.scan',
            'command.event.clear'
        ];
    }
}
