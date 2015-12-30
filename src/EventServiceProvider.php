<?php

namespace ProAI\Annotations;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEventBindings();
    }

    /**
     * Register the event bindings.
     *
     * @return void
     */
    protected function registerEventBindings()
    {
        if (! $this->app['files']->exists(storage_path('framework/events.php'))) {
            return;
        }

        $events = $this->app['events'];

        $listen = $this->app['files']->getRequire(storage_path('framework/events.php'));

        foreach ($listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }
}
