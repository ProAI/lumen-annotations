<?php

namespace ProAI\Datamapper\Presenter\Console;

use ProAI\Datamapper\Presenter\Console\PresenterCommand;

class PresenterRegisterCommand extends PresenterCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'presenter:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all presenters with @Presenter annotation.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // get classes
        $classes = $this->finder->getClassesFromNamespace($this->config['presenters_namespace']);

        // build metadata
        $presenters = $this->scanner->scan($classes);

        // save scanned presenters
        $this->repository->set($presenters);
        $this->repository->save();

        $this->info('Presenters registered successfully!');
    }
}
