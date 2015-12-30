<?php

namespace ProAI\Annotations\Events;

use Illuminate\Filesystem\Filesystem;

class Generator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path to events storage directory.
     *
     * @var array
     */
    protected $path;

    /**
     * path to events.php file.
     *
     * @var array
     */
    protected $eventsFile;

    /**
     * Constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param string $path
     * @param string $file
     * @return void
     */
    public function __construct(Filesystem $files, $path, $eventsFile)
    {
        $this->files = $files;
        $this->path = $path;
        $this->eventsFile = $this->path . '/' . $eventsFile;
    }

    /**
     * Generate routes from metadata and save to file.
     *
     * @param array $metadata
     * @param boolean $saveMode
     * @return void
     */
    public function generate($metadata)
    {
        // clean or make (if not exists) model storage directory
        if (! $this->files->exists($this->path)) {
            $this->files->makeDirectory($this->path);
        }

        // generate routes
        $routes = $this->generateEvents($metadata);

        // create events.php
        $this->files->put($this->eventsFile, $routes);
    }

    /**
     * Clean model directory.
     *
     * @return void
     */
    public function clean()
    {
        if ($this->files->exists($this->eventsFile)) {
            $this->files->delete($this->eventsFile);
        }
    }

    /**
     * Generate events from metadata.
     *
     * @param array $metadata
     * @return void
     */
    public function generateEvents($metadata)
    {
        $contents = '<?php' . PHP_EOL . PHP_EOL . 'return [' . PHP_EOL;

        foreach($metadata as $event => $eventHandlers) {
            $contents .= "    '" . $event . "' => [" . PHP_EOL;

            foreach($eventHandlers as $eventHandler) {
                $contents .= "        '" . $eventHandler . "'," . PHP_EOL;
            }

            $contents .= "    ]," . PHP_EOL;
        }

        $contents .= '];';

        return $contents;
    }
}
