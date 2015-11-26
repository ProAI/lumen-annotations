<?php

namespace ProAI\RouteAnnotations\Presenter\Metadata;

use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;

class PresenterScanner
{
    /**
     * The annotation reader instance.
     *
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $reader;

    /**
     * Create a new metadata builder instance.
     *
     * @param \Doctrine\Common\Annotations\AnnotationReader $reader
     * @return void
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
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
            $presenter = $this->parseClass($class);

            if ($presenter) {
                $metadata[$class] = $presenter;
            }
        }

        return $metadata;
    }

    /**
     * Parse a class.
     *
     * @param array $annotations
     * @return string|null
     */
    public function parseClass($class)
    {
        $reflectionClass = new ReflectionClass($class);

        // check if class is entity
        if ($annotation = $this->reader->getClassAnnotation($reflectionClass, '\ProAI\RouteAnnotations\Presenter\Annotations\Presenter')) {
            return $this->parsePresenter($class, $annotation);
        } else {
            return null;
        }
    }

    /**
     * Parse a controller class.
     *
     * @param array $class
     * @param \ProAI\RouteAnnotations\Annotations\Presenter $annotation
     * @return string
     */
    public function parsePresenter($class, $annotation)
    {
        return get_real_entity($annotation->class);
    }
}
