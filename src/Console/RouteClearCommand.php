<?php

namespace ProAI\Annotations\Console;

use Illuminate\Console\Command;
use ProAI\Annotations\Routing\Generator;

class RouteClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all registered routes.';

    /**
     * The routes generator instance.
     *
     * @var \ProAI\Annotations\Routing\Generator
     */
    protected $generator;

    /**
     * Create a new migration install command instance.
     *
     * @param \ProAI\Annotations\Routing\Generator $generator
     * @return void
     */
    public function __construct(Generator $generator)
    {
        parent::__construct();

        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // delete routes.php file
        $this->generator->clean();

        $this->info('Routes cleared successfully!');
    }

     public function handle()
    {
      $this->fire();
    }

}
