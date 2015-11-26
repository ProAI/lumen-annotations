<?php

namespace ProAI\RouteAnnotations\Annotations;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
final class Middleware implements Annotation
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var array
     */
    public $only;

    /**
     * @var array
     */
    public $except;
}
