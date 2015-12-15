<?php

namespace ProAI\Annotations\Metadata;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Filesystem\Filesystem;

class AnnotationLoader
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;
    
    /**
     * Create a new annotation loader instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @param string $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->files = $files;
        $this->path = $path;
    }

    
    /**
     * Register all annotations.
     *
     * @return void
     */
    public function registerAll()
    {
        foreach ($this->files->allFiles($this->path) as $file) {
            AnnotationRegistry::registerFile($file->getRealPath());
        }
    }
}
