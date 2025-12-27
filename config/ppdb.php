<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GTK Data Source
    |--------------------------------------------------------------------------
    |
    | Determines where to fetch GTK (Guru & Tenaga Kependidikan) data from.
    | 
    | Options:
    | - 'local': Use gtks table in ppdbv3 database (recommended for production)
    | - 'simansa': Direct connection to simansav3 database (same server only)
    |
    */
    'gtk_source' => env('GTK_SOURCE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | GTK Auto Sync
    |--------------------------------------------------------------------------
    |
    | Enable automatic synchronization from SIMANSA when accessing GTK menu.
    | Only works when gtk_source = 'local' and SIMANSA connection is available.
    |
    */
    'gtk_auto_sync' => env('GTK_AUTO_SYNC', false),

    /*
    |--------------------------------------------------------------------------
    | GTK Sync Interval
    |--------------------------------------------------------------------------
    |
    | Minimum time (in minutes) between automatic syncs.
    | Default: 60 minutes
    |
    */
    'gtk_sync_interval' => env('GTK_SYNC_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | SIMANSA Connection Available
    |--------------------------------------------------------------------------
    |
    | Check if SIMANSA database connection is configured and accessible.
    | Used to show/hide sync features in UI.
    |
    */
    'simansa_available' => env('SIMANSA_DB_DATABASE', null) !== null,
];
