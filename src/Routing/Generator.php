<?php

namespace ProAI\RouteAnnotations\Routing;

use Illuminate\Filesystem\Filesystem;

class Generator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path to routes storage directory.
     *
     * @var array
     */
    protected $path;

    /**
     * path to routes.php file.
     *
     * @var array
     */
    protected $routesFile;

    /**
     * Constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param string $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->files = $files;
        $this->path = $path;
        $this->routesFile = $this->path . '/routes.php';
    }

    /**
     * Generate routes from metadata.
     *
     * @param array $metadata
     * @param boolean $saveMode
     * @return void
     */
    public function generate($metadata)
    {
        // clean or make (if not exists) model storage directory
        if (! $this->files->exists($this->path)) {
            $this->files->makeDirectory($this->path);
        }

        // generate routes
        $routes = $this->generateRoutes($metadata);

        // create routes.php
        $this->files->put($this->routesFile, $routes);
    }

    /**
     * Clean model directory.
     *
     * @return void
     */
    public function clean()
    {
        if ($this->files->exists($this->routesFile)) {
            $this->files->delete($this->routesFile);
        }
    }

    /**
     * Generate model from metadata.
     *
     * @param array $metadata
     * @return void
     */
    public function generateRoutes($metadata)
    {
        $contents = '<?php' . PHP_EOL . PHP_EOL;

        $routes = [];

        foreach($metadata as $controllerMetadata) {
            foreach($controllerMetadata as $routeMetadata) {
                $options = [];

                // as option
                if (isset($routeMetadata['as'])) {
                    $options = "'as' => '".$routeMetadata['as']."'";
                }

                // middleware option
                if (isset($routeMetadata['middleware'])) {
                    $options = "'middleware' => '".$routeMetadata['middleware']."'";
                }

                // uses option
                $options = "'uses' => '".$routeMetadata['controller']."@".$routeMetadata['controllerMethod']."'";

                // url
                $url = $routeMetadata['url'];
                if (isset($routeMetadata['prefix'])) {
                    $url = $routeMetadata['prefix']."/".$routeMetadata['url'];
                }

                $routes[] = "$app->".strtolower($routeMetadata['httpMethod'])."('".$url."', [".implode(", ", $options)."]);";
            }
        }

        $contents = implode(PHP_EOL, $routes);

        return $contents;
    }
}
