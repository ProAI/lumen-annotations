<?php

namespace ProAI\RouteAnnotations\Metadata;

use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;

class RouteScanner
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
            $controllerMetadata = $this->parseClass($class);

            if ($controller) {
                $metadata[$class] = $controllerMetadata;
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

        // check if class is entity
        if ($annotation = $this->reader->getClassAnnotation($reflectionClass, '\ProAI\RouteAnnotations\Annotations\Controller')) {
            return $this->parseController($class);
        } else {
            return null;
        }
    }

    /**
     * Parse a controller class.
     *
     * @param string $class
     * @return string
     */
    public function parseController($class)
    {
        $reflectionClass = new ReflectionClass($class);
        $classAnnotations = $this->reader->getClassAnnotations($reflectionClass);

        $controllerMetadata = [];

        // find entity parameters and plugins
        foreach ($classAnnotations as $annotation) {
            // controller attributes
            if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Controller) {
                $prefix = $annotation->prefix;
                $middleware = $annotation->middleware;
            }
            if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Middleware) {
                $middleware = $annotation->value;
            }
        }
        
        // find routes
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $name = $reflectionMethod->getName();
            $methodAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);

            $route = false;

            foreach ($methodAnnotations as $annotation) {
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Get) {
                    $route = true;
                    $httpMethod= 'GET';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Post) {
                    $route = true;
                    $httpMethod= 'POST';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Options) {
                    $route = true;
                    $httpMethod= 'OPTIONS';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Put) {
                    $route = true;
                    $httpMethod= 'PUT';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Patch) {
                    $route = true;
                    $httpMethod= 'PATCH';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Delete) {
                    $route = true;
                    $httpMethod= 'DELETE';
                }
                if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Any) {
                    $route = true;
                    $httpMethod= 'ANY';
                }

                if ($route) {
                    $routeAnnotation = $annotation;
                }
            }

            if ($route) {

                // init new route metadata
                $routeMetadata = [
                    'url' => $annotation->value,
                    'controller' => $class,
                    'controllerMethod' => $name,
                    'httpMethod' => $method,
                ];

                // add as and middleware
                if ($annotation->as) {
                    $routeMetadata['as'] = $annotation->as;
                }
                if ($annotation->middleware) {
                    $routeMetadata['middleware'] = $annotation->middleware;
                }

                // add other method annotations
                foreach ($methodAnnotations as $annotation) {
                    if ($annotation instanceof \ProAI\RouteAnnotations\Annotations\Middleware) {
                        $routeMetadata['middleware'] = $annotation->value;
                    }
                }

                // add global prefix and middleware
                if (isset($prefix)) {
                    $routeMetadata['prefix'] = $prefix;
                }
                if (isset($middleware) && ! in_array()) {
                    $routeMetadata['middleware'] = $middleware;
                }

                $controllerMetadata[$name] = $routeMetadata;
            }
        }

        return $controllerMetadata;
    }
}
