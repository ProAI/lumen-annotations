<?php

namespace ProAI\Annotations\Annotations;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Hears implements Annotation
{
    /**
     * @var string
     */
    public $value;
}
