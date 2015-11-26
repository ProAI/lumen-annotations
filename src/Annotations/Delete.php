<?php

namespace ProAI\RouteAnnotations\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Delete implements Annotation
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

    /**
     * @var array
     */
    public $where;

    /**
     * @var string
     */
    public $domain;
}
