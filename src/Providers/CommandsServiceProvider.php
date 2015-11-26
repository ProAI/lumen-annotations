<?php

namespace ProAI\RouteAnnotations\Providers;

use Illuminate\Support\ServiceProvider;
use ProAI\RouteAnnotations\Metadata\RouteScanner;
use ProAI\RouteAnnotations\Routing\Generator;
use ProAI\RouteAnnotations\Console\RegisterCommand;
use ProAI\RouteAnnotations\Console\ClearCommand;

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
        $this->app->register('ProAI\RouteAnnotations\Providers\MetadataServiceProvider');

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
        $this->app->singleton('route.annotations.scanner', function ($app) {
            $reader = $app['route.annotations.annotationreader'];

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

        $app->singleton('route.annotations.generator', function ($app) {
            $path = $app['path.storage'] . '/framework/entities';

            return new Generator($app['files'], $path);
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
        $commands = array('Register', 'Clear');

        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }

        // register commands
        $this->commands(
            'command.route.register',
            'command.route.clear'
        );
    }

    /**
     * Register the "presenter:register" command.
     *
     * @return void
     */
    protected function registerRegisterCommand()
    {
        $this->app->singleton('command.route.register', function ($app) {
            return new PresenterRegisterCommand(
                $app['route.annotations.classfinder'],
                $app['route.annotations.scanner'],
                $app['route.annotations.generator'],
                $app['config']['route.annotations']
            );
        });
    }

    /**
     * Register the "presenter:clear" command.
     *
     * @return void
     */
    protected function registerClearCommand()
    {
        $this->app->singleton('command.route.clear', function ($app) {
            return new PresenterClearCommand(
                $app['route.annotations.classfinder'],
                $app['route.annotations.scanner'],
                $app['route.annotations.generator'],
                $app['config']['route.annotations']
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
            'route.annotations.scanner',
            'route.annotations.generator',
            'command.route.register',
            'command.route.clear'
        ];
    }
}
