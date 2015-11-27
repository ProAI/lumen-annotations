<?php

namespace ProAI\RouteAnnotations\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Any implements Annotation
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $as;

    /**
     * @var mixed
     */
    public $middleware;
}
