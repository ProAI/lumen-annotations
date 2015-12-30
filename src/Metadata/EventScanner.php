<?php

namespace ProAI\Annotations\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class EventScanner
{
    /**
     * The annotation reader instance.
     *
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $reader;

    /**
     * The config of the event annotations package.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new metadata builder instance.
     *
     * @param \Doctrine\Common\Annotations\AnnotationReader $reader
     * @param array $config
     * @return void
     */
    public function __construct(AnnotationReader $reader, $config)
    {
        $this->reader = $reader;
        $this->config = $config;
    }

    /**
     * Build metadata from all entity classes.
     *
     * @param array $classes
     * @return array
     */
    public function scan($classes)
    {
        $metadata = [];

        foreach ($classes as $class) {
            $eventListenerMetadata = $this->parseClass($class);

            if ($eventListenerMetadata) {
                $metadata[$eventListenerMetadata][] = $class;
            }
        }

        return $metadata;
    }

    /**
     * Parse a class.
     *
     * @param string $class
     * @return array|null
     */
    public function parseClass($class)
    {
        $reflectionClass = new ReflectionClass($class);

        // check if class is controller
        if ($annotation = $this->reader->getClassAnnotation($reflectionClass, '\ProAI\Annotations\Annotations\Hears')) {
            $class = $annotation->value;

            if (isset($this->config['events_namespace']) && substr($class, 0, strlen($this->config['events_namespace'])) != $this->config['events_namespace']) {
                $class = $this->config['events_namespace'].'\\'.$class;
            }

            return $class;
        } else {
            return null;
        }
    }
}
