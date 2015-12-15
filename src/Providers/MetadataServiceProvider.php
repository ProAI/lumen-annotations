<?php

namespace ProAI\Annotations\Providers;

use Illuminate\Support\ServiceProvider;
use ProAI\Annotations\Metadata\ClassFinder;
use Illuminate\Filesystem\ClassFinder as FilesystemClassFinder;
use ProAI\Annotations\Metadata\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;

class MetadataServiceProvider extends ServiceProvider
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
        $this->registerAnnotations();

        $this->registerAnnotationReader();

        $this->registerClassFinder();
    }

    /**
     * Registers all annotation classes
     *
     * @return void
     */
    public function registerAnnotations()
    {
        $app = $this->app;

        $loader = new AnnotationLoader($app['files'], __DIR__ . '/../Annotations');

        $loader->registerAll();
    }

    /**
     * Register the class finder implementation.
     *
     * @return void
     */
    protected function registerAnnotationReader()
    {
        $this->app->singleton('annotations.annotationreader', function ($app) {
            return new AnnotationReader;
        });
    }

    /**
     * Register the class finder implementation.
     *
     * @return void
     */
    protected function registerClassFinder()
    {
        $this->app->singleton('annotations.classfinder', function ($app) {
            $finder = new FilesystemClassFinder;

            return new ClassFinder($finder);
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
            'annotations.classfinder',
            'annotations.annotationreader',
        ];
    }
}
