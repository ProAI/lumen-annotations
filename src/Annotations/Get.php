<?php

namespace ProAI\RouteAnnotations\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Get implements Annotation
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
