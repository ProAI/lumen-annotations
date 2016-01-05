<?php

namespace ProAI\Annotations\Metadata;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Filesystem\ClassFinder as FilesystemClassFinder;

class ClassFinder
{
    /**
     * The class finder instance.
     *
     * @var \Illuminate\Filesystem\ClassFinder
     */
    protected $finder;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Create a new metadata builder instance.
     *
     * @param \Illuminate\Filesystem\ClassFinder $finder
     * @param array $config
     * @return void
     */
    public function __construct(FilesystemClassFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Get all classes for a given namespace.
     *
     * @param string $namespace
     * @return array
     */
    public function getClassesFromNamespace($namespace = null)
    {
        $namespace = $namespace ?: $this->getAppNamespace();

        $path = $this->convertNamespaceToPath($namespace);

        return $this->finder->findClasses($path);
    }

    /**
     * Convert given namespace to file path.
     *
     * @param string $namespace
     * @return string|null
     */
    protected function convertNamespaceToPath($namespace)
    {
        // strip app namespace
        $appNamespace = $this->getAppNamespace();

        if (substr($namespace, 0, strlen($appNamespace)) != $appNamespace) {
            return null;
        }

        $subNamespace = substr($namespace, strlen($appNamespace));

        // replace \ with / to get the correct file path
        $subPath = str_replace('\\', '/', $subNamespace);

        // create path
        return app('path') . '/' . $subPath;
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getAppNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents(base_path().'/composer.json'), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(app('path')) == realpath(base_path().'/'.$pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }
        
        throw new RuntimeException('Unable to detect application namespace.');
    }
}
