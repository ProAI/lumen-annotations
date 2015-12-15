<?php

namespace ProAI\Annotations\Annotations;

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
}
