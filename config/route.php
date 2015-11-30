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
    | Automatically scan controller classes and update database on page load.
    | This option is useful in development mode.
    |
    */
    
    'auto_scan' => env('APP_AUTO_SCAN', false),

];