<?php

namespace ProAI\Annotations\Metadata;

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

            if ($controllerMetadata) {
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

        // check if class is controller
        if ($annotation = $this->reader->getClassAnnotation($reflectionClass, '\ProAI\Annotations\Annotations\Controller')) {
            return $this->parseController($class);
        } else {
            return null;
        }
    }

    /**
     * Parse a controller class.
     *
     * @param string $class
     * @return array
     */
    public function parseController($class)
    {
        $reflectionClass = new ReflectionClass($class);
        $classAnnotations = $this->reader->getClassAnnotations($reflectionClass);

        $controllerMetadata = [];
        $middleware = [];

        // find entity parameters and plugins
        foreach ($classAnnotations as $annotation) {
            // controller attributes
            if ($annotation instanceof \ProAI\Annotations\Annotations\Controller) {
                $prefix = $annotation->prefix;
                $middleware = $this->addMiddleware($middleware, $annotation->middleware);
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Middleware) {
                $middleware = $this->addMiddleware($middleware, $annotation->value);
            }

            // resource controller
            if ($annotation instanceof \ProAI\Annotations\Annotations\Resource) {
                $resourceMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
                if (! empty($annotation->only)) {
                    $resourceMethods = array_intersect($resourceMethods, $annotation->only);
                }
                elseif (! empty($annotation->except)) {
                    $resourceMethods = array_diff($resourceMethods, $annotation->except);
                }
                $resource = [
                    'name' => $annotation->value,
                    'methods' => $resourceMethods
                ];
            }
        }
        
        // find routes
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $name = $reflectionMethod->getName();
            $methodAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);
            $routeMetadata = [];

            // controller method is resource route
            if (! empty($resource) && in_array($name, $resource['methods'])) {
                $routeMetadata = [
                    'uri' => $resource['name'].$this->getResourcePath($name),
                    'controller' => $class,
                    'controllerMethod' => $name,
                    'httpMethod' => $this->getResourceHttpMethod($name),
                    'as' => $resource['name'].'.'.$name,
                    'middleware' => ''
                ];
            }

            // controller method is route
            if ($route = $this->hasHttpMethodAnnotation($name, $methodAnnotations)) {
                $routeMetadata = [
                    'uri' => $route['uri'],
                    'controller' => $class,
                    'controllerMethod' => $name,
                    'httpMethod' => $route['httpMethod'],
                    'as' => $route['as'],
                    'middleware' => $route['middleware']
                ];
            }

            // add more route options to route metadata
            if (! empty($routeMetadata)) {
                if (! empty($middleware)) {
                    $routeMetadata['middleware'] = $middleware;
                }

                // add other method annotations
                foreach ($methodAnnotations as $annotation) {
                    if ($annotation instanceof \ProAI\Annotations\Annotations\Middleware) {
                        $middleware = $this->addMiddleware($middleware, $routeMetadata['middleware']);
                    }
                }

                // add global prefix and middleware
                if (! empty($prefix)) {
                    $routeMetadata['uri'] = $prefix.'/'.$routeMetadata['uri'];
                }

                $controllerMetadata[$name] = $routeMetadata;
            }
        }

        return $controllerMetadata;
    }

    /**
     * Get resource http method.
     *
     * @param string $method
     * @return string
     */
    protected function getResourceHttpMethod($method)
    {
        $resourceHttpMethods = [
            'index' => 'GET',
            'create' => 'GET',
            'store' => 'POST',
            'show' => 'GET',
            'edit' => 'GET',
            'update' => 'PUT',
            'destroy' => 'DELETE'
        ];

        return (isset($resourceHttpMethods[$method])) ? $resourceHttpMethods[$method] : null;
    }

    /**
     * Get resource path.
     *
     * @param string $method
     * @return string
     */
    protected function getResourcePath($method)
    {
        $resourcePaths = [
            'index' => '',
            'create' => 'create',
            'store' => '',
            'show' => '/{id}',
            'edit' => '/{id}/edit',
            'update' => '/{id}',
            'destroy' => '/{id}'
        ];

        return (isset($resourcePaths[$method])) ? $resourcePaths[$method] : null;
    }

    /**
     * Check for http method.
     *
     * @param string $name
     * @param array $methodAnnotations
     * @return string
     */
    protected function hasHttpMethodAnnotation($name, $methodAnnotations)
    {
        foreach ($methodAnnotations as $annotation) {
            // check for http method annotation
            if ($annotation instanceof \ProAI\Annotations\Annotations\Get) {
                $httpMethod = 'GET';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Post) {
                $httpMethod = 'POST';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Options) {
                $httpMethod = 'OPTIONS';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Put) {
                $httpMethod = 'PUT';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Patch) {
                $httpMethod = 'PATCH';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Delete) {
                $httpMethod = 'DELETE';
                break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Any) {
                $httpMethod = 'ANY';
                break;
            }

        }

        // http method found
        if (! empty($httpMethod)) {
            // options
            $as = (! empty($annotation->as)) ? $annotation->as : '';

            $uri = (empty($annotation->value)) ? str_replace("_", "-", snake_case($name)) : $annotation->value;

            return [
                'uri' => $uri,
                'httpMethod' => $httpMethod,
                'as' => $as,
                'middleware' => $this->addMiddleware([], $annotation->middleware)
            ];
        }

        return null;
    }

    /**
     * Add middleware
     *
     * @param array $middleware
     * @param array $newMiddleware
     * @return array
     */
    protected function addMiddleware($middleware, $newMiddleware)
    {
        if (! empty($newMiddleware)) {
            $newMiddleware = (is_array($newMiddleware))
                ? $newMiddleware
                : [$newMiddleware];

            return array_merge($middleware, $newMiddleware);
        }

        return $middleware;
    }
}
