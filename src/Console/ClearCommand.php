<?php

namespace ProAI\Datamapper\Presenter\Console;

use ProAI\Datamapper\Presenter\Console\PresenterCommand;

class PresenterClearCommand extends PresenterCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'presenter:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all registered presenters.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // delete presenters.json file
        $this->repository->delete();

        $this->info('Presenters cleared successfully!');
    }
}
