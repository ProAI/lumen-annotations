<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes Namespace
    |--------------------------------------------------------------------------
    |
    | Only the classes in this namespace will be scanned by the
    | `php artisan route:scan` command.
    |
    */
    
    'routes_namespace' => 'App\Http\Controllers',

    /*
    |--------------------------------------------------------------------------
    | Events Namespace
    |--------------------------------------------------------------------------
    |
    | Only the classes in this namespace will be scanned by the
    | `php artisan event:scan` command.
    |
    */
    
    'events_namespace' => 'App\Http\Handlers\Events',

    /*
    |--------------------------------------------------------------------------
    | Auto Scan
    |--------------------------------------------------------------------------
    |
    | Automatically scan controller classes for routes and event handler
    | classes for event bindings and update routes and event bindings. This
    | option is useful in development mode.
    |
    */
    
    'auto_scan' => env('APP_AUTO_SCAN', false),

];