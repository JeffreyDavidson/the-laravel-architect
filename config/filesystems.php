<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'nas-backups' => [
            'driver' => 'sftp',
            'host' => env('BACKUP_SFTP_HOST'),
            'username' => env('BACKUP_SFTP_USERNAME'),
            'password' => env('BACKUP_SFTP_PASSWORD'),
            'port' => (int) env('BACKUP_SFTP_PORT', 22),
            'root' => env('BACKUP_SFTP_ROOT', '/laravel-backups'),
            'hostFingerprint' => env('BACKUP_SFTP_HOST_FINGERPRINT'),
            'timeout' => (int) env('BACKUP_SFTP_TIMEOUT', 30),
            'maxTries' => (int) env('BACKUP_SFTP_MAX_TRIES', 3),
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'throw' => true,
            'report' => true,
        ],

        'b2-backups' => [
            'driver' => 's3',
            'key' => env('BACKUP_B2_KEY_ID'),
            'secret' => env('BACKUP_B2_APPLICATION_KEY'),
            'region' => env('BACKUP_B2_REGION', 'us-east-005'),
            'bucket' => env('BACKUP_B2_BUCKET'),
            'endpoint' => env('BACKUP_B2_ENDPOINT'),
            'use_path_style_endpoint' => false,
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'throw' => true,
            'report' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
