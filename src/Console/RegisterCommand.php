<?php

namespace ProAI\RouteAnnotations\Console;

use Illuminate\Console\Command;
use ProAI\RouteAnnotations\Metadata\ClassFinder;
use ProAI\RouteAnnotations\Metadata\RouteScanner;
use ProAI\RouteAnnotations\Routing\Generator;

class RegisterCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all routes with route annotations.';

    /**
     * The class finder instance.
     *
     * @var \ProAI\RouteAnnotations\Metadata\ClassFinder
     */
    protected $finder;

    /**
     * The route scanner instance.
     *
     * @var \ProAI\RouteAnnotations\Metadata\RouteScanner
     */
    protected $scanner;

    /**
     * The routes generator instance.
     *
     * @var \ProAI\RouteAnnotations\Routing\Generator
     */
    protected $generator;

    /**
     * The config of the route annotations package.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new migration install command instance.
     *
     * @param \ProAI\RouteAnnotations\Metadata\ClassFinder $finder
     * @param \ProAI\RouteAnnotations\Metadata\RouteScanner $scanner
     * @param \ProAI\RouteAnnotations\Routing\Generator $generator
     * @param array $config
     * @return void
     */
    public function __construct(ClassFinder $finder, RouteScanner $scanner, Generator $generator, $config)
    {
        parent::__construct();

        $this->finder = $finder;
        $this->scanner = $scanner;
        $this->generator = $generator;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // get classes
        $classes = $this->finder->getClassesFromNamespace($this->config['controllers_namespace']);

        // build metadata
        $routes = $this->scanner->scan($classes);

        // generate routes.php file for scanned routes
        $this->generator->generate($routes);

        $this->info('Routes registered successfully!');
    }
}
