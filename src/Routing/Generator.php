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
     * @param string $file
     * @return void
     */
    public function __construct(Filesystem $files, $path, $routesFile)
    {
        $this->files = $files;
        $this->path = $path;
        $this->routesFile = $this->path . '/' . $routesFile;
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
        $contents = '<?php' . PHP_EOL;

        $routes = [];

        foreach($metadata as $name => $controllerMetadata) {
            $contents .= PHP_EOL . "// Routes in controller '" . $name . "'" . PHP_EOL;

            foreach($controllerMetadata as $routeMetadata) {
                $options = [];

                // as option
                if (! empty($routeMetadata['as'])) {
                    $options[] = "'as' => '".$routeMetadata['as']."'";
                }

                // middleware option
                if (! empty($routeMetadata['middleware'])) {
                    if (is_array($routeMetadata['middleware'])) {
                        $middleware = "['".implode("', '",$routeMetadata['middleware'])."']";
                    } else {
                        $middleware = "'".$routeMetadata['middleware']."'";
                    }
                    $options[] = "'middleware' => ".$middleware;
                }

                // uses option
                $options[] = "'uses' => '".$routeMetadata['controller']."@".$routeMetadata['controllerMethod']."'";

                $contents .= "\$app->".strtolower($routeMetadata['httpMethod'])."('".$routeMetadata['uri']."', [".implode(", ", $options)."]);" . PHP_EOL;
            }
        }


        return $contents;
    }
}
