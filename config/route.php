<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Controllers Namespace
    |--------------------------------------------------------------------------
    |
    | Only the classes in this namespace will be scanned by the
    | `php artisan route:create` command.
    |
    */
    
    'controllers_namespace' => 'App\Http\Controllers',

    /*
    |--------------------------------------------------------------------------
    | Auto Scan
    |--------------------------------------------------------------------------
    |
    | Automatically scan entity classes and update database on page load. This
    | Option is useful in development mode.
    |
    */
    
    'auto_scan' => env('APP_AUTO_SCAN', false),

];