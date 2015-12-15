<?php

namespace ProAI\Annotations\Providers;

use Illuminate\Support\ServiceProvider;
use ProAI\Annotations\Metadata\RouteScanner;
use ProAI\Annotations\Routing\Generator;
use ProAI\Annotations\Console\RouteScanCommand;
use ProAI\Annotations\Console\RouteClearCommand;

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

        $this->registerScanner();

        $this->registerGenerator();

        $this->registerCommands();
    }

    /**
     * Register the scanner implementation.
     *
     * @return void
     */
    protected function registerScanner()
    {
        $this->app->singleton('annotations.route.scanner', function ($app) {
            $reader = $app['annotations.annotationreader'];

            return new RouteScanner($reader);
        });
    }

    /**
     * Register the generator implementation.
     *
     * @return void
     */
    protected function registerGenerator()
    {
        $app = $this->app;

        $app->singleton('annotations.route.generator', function ($app) {
            $path = storage_path('framework');

            return new Generator($app['files'], $path, 'routes.php');
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
        $commands = array('RouteScan', 'RouteClear');

        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        // register commands
        $this->commands(
            'command.route.scan',
            'command.route.clear'
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
                $app['annotations.scanner'],
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
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'annotations.route.scanner',
            'annotations.route.generator',
            'command.route.scan',
            'command.route.clear'
        ];
    }
}
