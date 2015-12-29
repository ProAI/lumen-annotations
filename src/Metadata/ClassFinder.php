<?php

namespace ProAI\Annotations\Metadata;

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

        if (! substr($namespace, 0, strlen($appNamespace)) == $appNamespace) {
            return null;
        }

        $subNamespace = substr($namespace, strlen($appNamespace));

        // replace \ with / to get the correct file path
        $subPath = str_replace('\\', '/', $subNamespace);

        // create path
        return app('path') . '/' . $subPath;
    }
}
