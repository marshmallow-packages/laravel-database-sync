<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remote Database Configuration
    |--------------------------------------------------------------------------
    |
    | These options define the connection details for the remote database
    | that will be used during the synchronization process. Ensure these
    | values are set in your environment file for security purposes.
    |
    */

    'remote_user_and_host' => env('DATABASE_SYNC_REMOTE_USER_AND_HOST'),
    'remote_database' => env('DATABASE_SYNC_REMOTE_DATABASE', env('DB_DATABASE')),
    'remote_database_username' => env('DATABASE_SYNC_REMOTE_DATABASE_USERNAME'),
    'remote_database_password' => env('DATABASE_SYNC_REMOTE_DATABASE_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Local Database Configuration
    |--------------------------------------------------------------------------
    |
    | These options define the connection details for the local database
    | that will be used during the synchronization process. You can set
    | default values here or override them in your environment file.
    |
    */

    'local_host' => env('DATABASE_SYNC_LOCAL_HOST', env('DB_HOST', '127.0.0.1')),
    'local_database' => env('DATABASE_SYNC_LOCAL_DATABASE', env('DB_DATABASE')),
    'local_database_username' => env('DATABASE_SYNC_LOCAL_DATABASE_USERNAME', env('DB_USERNAME', 'root')),
    'local_database_password' => env('DATABASE_SYNC_LOCAL_DATABASE_PASSWORD', env('DB_PASSWORD', 'secret')),

    /*
    |--------------------------------------------------------------------------
    | Temporary File Locations
    |--------------------------------------------------------------------------
    |
    | During the synchronization process, temporary SQL files may be created
    | to store database dumps. These options specify the file paths for
    | both the remote and local environments.
    |
    */

    'temporary_file_location' => [
        'remote' => env('DATABASE_SYNC_TEMPORARY_FILE_LOCATION_REMOTE', '~/new_data.sql'),
        'local' => env('DATABASE_SYNC_TEMPORARY_FILE_LOCATION_LOCAL', '~/Downloads/new_data.sql'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | The "ignore" option allows you to specify tables that should be excluded
    | from the synchronization process. Add any table names here that you
    | do not want to be synced between the databases.
    |
    */

    'tables' => [
        'ignore' => [
            'jobs',
            'migrations',
            'failed_jobs',
            'action_events',
            'password_resets',
            'telescope_entries',

            /** Ignore the Pulse tables */
            'pulse_values',
            'pulse_entries',
            'pulse_aggregates',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Synchronization Suites
    |--------------------------------------------------------------------------
    |
    | Define custom synchronization suites here. Suites allow you to group
    | specific tables for targeted synchronization tasks.
    | Leave this empty if you do not need custom suites.
    |
    */

    'suites' => [],

    /*
    |--------------------------------------------------------------------------
    | Sync Behavior Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the sync process handles file transfers:
    | - 'batch': (default) Dump all tables to one file and transfer once
    | - 'individual': Transfer each table separately (legacy behavior)
    |
    */

    'file_transfer_mode' => env('DATABASE_SYNC_FILE_TRANSFER_MODE', 'batch'),

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Support
    |--------------------------------------------------------------------------
    |
    | If your application uses a multi-tenant architecture, you can enable
    | this option to handle synchronization for multiple tenants. Set this
    | to "true" if multi-tenancy is required.
    |
    */

    'multi_tenant' => false,

    'mysql' => [
        'dump_action_flags' => '--skip-lock-tables --no-create-info --complete-insert --skip-triggers --replace',
    ],

    /*
    |--------------------------------------------------------------------------
    | Process Timeout Configuration
    |--------------------------------------------------------------------------
    |
    | Set the timeout (in seconds) for long-running database operations.
    | Set to null to disable timeout entirely for very large databases.
    | Default: 300 seconds (5 minutes)
    |
    */

    'process_timeout' => env('DATABASE_SYNC_PROCESS_TIMEOUT', 300),
];
