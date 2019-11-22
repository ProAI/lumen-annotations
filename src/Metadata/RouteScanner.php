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

        // find entity parameters and plugins
        foreach ($classAnnotations as $annotation) {
            // controller attributes
            if ($annotation instanceof \ProAI\Annotations\Annotations\Controller) {
                $prefix = $annotation->prefix;
                $middleware = $annotation->middleware;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Middleware) {
                $middleware = $annotation->value;
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
            if ($routes = $this->hasHttpMethodAnnotation($name, $methodAnnotations)) {
              $routeMetadata = [];
              foreach($routes  as $route){
                $routeMetadata[] = [
                    'uri' => $route['uri'],
                    'controller' => $class,
                    'controllerMethod' => $name,
                    'httpMethod' => $route['httpMethod'],
                    'as' => $route['as'],
                    'middleware' => $route['middleware']
                ];
              }
            }

            // add more route options to route metadata
            if (! empty($routeMetadata)) {  
                if(!isset($routeMetadata[0])){
                  $temp =  [];
                  $temp[] = $routeMetadata;
                  $routeMetadatas  = $temp;
                } else {
                  $routeMetadatas  = $routeMetadata;
                }
                $idx = 0;
                foreach($routeMetadatas as $routeMetadata){
                  $idx++;

                // add other method annotations
                foreach ($methodAnnotations as $annotation) {
                  if ($annotation instanceof \ProAI\Annotations\Annotations\Middleware) {
                      if (!empty($middleware) && isset($routeMetadata['middleware'])) {
                          $routeMetadata['middleware'] = [$middleware, $annotation->value];
                          continue;
                      }

                      $routeMetadata['middleware'] = $annotation->value;
                  }
                }

                  // add global prefix and middleware
                  if (! empty($prefix)) {
                    $routeMetadata['uri'] = $prefix.'/'.$routeMetadata['uri'];
                  }
                  if (! empty($middleware) && empty($routeMetadata['middleware'])) {
                    $routeMetadata['middleware'] = $middleware;
                  }

                  $controllerMetadata[$name.$idx] = $routeMetadata;
              }
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

        $parseAnnotation = function ($httpMethod,$annotation){
          // options
          $as         = (! empty($annotation->as))          ? $annotation->as : '';
          $middleware = (! empty($annotation->middleware))  ? $annotation->middleware : '';

          $uri = (empty($annotation->value)) ? str_replace("_", "-", snake_case($name)) : $annotation->value;
          return [
                  'uri' => $uri,
                  'httpMethod' => $httpMethod,
                  'as' => $as,
                  'middleware' => $middleware
          ];

        };


        $return = [];

        foreach ($methodAnnotations as $annotation) {
            // check for http method annotation
            if ($annotation instanceof \ProAI\Annotations\Annotations\Get) {
                $httpMethod = 'GET';
                $return[] = $parseAnnotation($httpMethod,$annotation);
           //     break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Post) {
                $httpMethod = 'POST';
                $return[] = $parseAnnotation($httpMethod,$annotation);
                //break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Options) {
                $httpMethod = 'OPTIONS';
                $return[] = $parseAnnotation($httpMethod,$annotation);
               // break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Put) {
                $httpMethod = 'PUT';
                $return[] = $parseAnnotation($httpMethod,$annotation);
                //break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Patch) {
                $httpMethod = 'PATCH';
                $return[] = $parseAnnotation($httpMethod,$annotation);
               // break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Delete) {
                $httpMethod = 'DELETE';
                $return[] = $parseAnnotation($httpMethod,$annotation);
                //break;
            }
            if ($annotation instanceof \ProAI\Annotations\Annotations\Any) {
                $httpMethod = 'ANY';
                $return[] = $parseAnnotation($httpMethod,$annotation);
                //break;
            }

        }

        if(count($return) > 0){
          return $return;
        } else {
          return false;
        }
    }
}
