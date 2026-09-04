<?php

return [
    'production' => [
        'php_binary' => env('CONTENT_SYNC_PRODUCTION_PHP_BINARY', '/usr/bin/php8.4'),
        'site_path' => env('CONTENT_SYNC_PRODUCTION_SITE_PATH', '/home/forge/thelaravelarchitect.com/current'),
    ],

    'staging_author' => [
        'name' => env('CONTENT_SYNC_AUTHOR_NAME', 'Jeffrey Davidson'),
        'email' => env('CONTENT_SYNC_AUTHOR_EMAIL', 'content-author@thelaravelarchitect.invalid'),
    ],
];
