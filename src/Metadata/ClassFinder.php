<?php

namespace ProAI\RouteAnnotations\Metadata;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Filesystem\ClassFinder as FilesystemClassFinder;

class ClassFinder
{
    use AppNamespaceDetectorTrait;

    /**
     * The class finder instance.
     *
     * @var \Illuminate\Filesystem\ClassFinder
     */
    protected $finder;

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
     * Get all classes for a namespace.
     *
     * @param string namespace
     * @return array
     */
    public function getClassesFromNamespace($namespace=null)
    {
        $base_namespace = $namespace ?: $this->getAppNamespace();

        $path = $this->stripNamespace($base_namespace, $this->getAppNamespace());

        $directory = app('path') . '/' . $path;

        return $this->finder->findClasses($directory);
    }

    /**
     * Strip given namespace from class.
     *
     * @param string|object $class
     * @param string $namespace
     * @return string|null
     */
    protected function stripNamespace($class, $namespace)
    {
        $class = (is_object($class)) ? get_class($class) : $class;

        if (substr($class, 0, strlen($namespace)) == $namespace) {
            return substr($class, strlen($namespace));
        }
        
        return null;
    }
}
