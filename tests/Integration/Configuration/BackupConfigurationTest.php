<?php

it('uses safe backup configuration defaults', function () {
    expect(config('backup.backup.source.files.include'))
        ->not->toContain(base_path())
        ->not->toContain(null)
        ->and(config('backup.backup.source.files.exclude'))
        ->toContain(base_path('.env'))
        ->and(config('backup.backup.destination.disks'))
        ->toBe(['local'])
        ->and(config('backup.monitor_backups.0.disks'))
        ->toBe(['local'])
        ->and(config('backup.notifications.mail.to'))
        ->toBe(config('mail.contact_to'));
});

it('configures a private S3-compatible disk for Backblaze backups', function () {
    expect(config('filesystems.disks.b2-backups'))
        ->toMatchArray([
            'driver' => 's3',
            'key' => null,
            'secret' => null,
            'region' => 'us-east-005',
            'bucket' => null,
            'endpoint' => 'https://s3.us-east-005.backblazeb2.com',
            'use_path_style_endpoint' => false,
            'visibility' => 'private',
            'directory_visibility' => 'private',
            'throw' => true,
            'report' => true,
        ]);
});

it('backs up the default database connection when DB_CONNECTION is unset', function () {
    $environment = [
        'getenv' => getenv('DB_CONNECTION'),
        '_ENV' => $_ENV['DB_CONNECTION'] ?? null,
        '_SERVER' => $_SERVER['DB_CONNECTION'] ?? null,
    ];
    putenv('DB_CONNECTION');
    unset($_ENV['DB_CONNECTION'], $_SERVER['DB_CONNECTION']);

    try {
        $backup = require config_path('backup.php');
        $database = require config_path('database.php');
    } finally {
        if (is_string($environment['getenv'])) {
            putenv("DB_CONNECTION={$environment['getenv']}");
        }

        if ($environment['_ENV'] !== null) {
            $_ENV['DB_CONNECTION'] = $environment['_ENV'];
        }

        if ($environment['_SERVER'] !== null) {
            $_SERVER['DB_CONNECTION'] = $environment['_SERVER'];
        }
    }

    expect(data_get($backup, 'backup.source.databases'))
        ->toBe([data_get($database, 'default')])
        ->toBe(['sqlite']);
});
