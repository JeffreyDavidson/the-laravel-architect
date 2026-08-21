<?php

beforeEach(function () {
    config()->set([
        'app.env' => 'production',
        'app.debug' => false,
        'app.url' => 'https://thelaravelarchitect.com',
        'app.key' => 'base64:production-key',
        'app.admin_email' => 'admin@thelaravelarchitect.com',
        'app.content_author_email' => 'author@thelaravelarchitect.com',
        'session.secure' => true,
        'queue.default' => 'database',
        'mail.default' => 'resend',
        'mail.contact_to' => 'contact@thelaravelarchitect.com',
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => '/var/www/the-laravel-architect/database/database.sqlite',
        'backup.backup.destination.disks' => ['local', 's3'],
        'backup.backup.password' => 'encrypted-archive-password',
        'backup.notifications.mail.to' => 'backups@thelaravelarchitect.com',
    ]);
});

it('accepts a safe production configuration', function () {
    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is ready.')
        ->assertSuccessful();
});

it('reports every unsafe production setting without exposing its value', function () {
    config()->set([
        'app.debug' => true,
        'app.url' => 'http://localhost',
        'app.admin_email' => 'admin@example.test',
        'session.secure' => false,
        'queue.default' => 'sync',
        'database.connections.sqlite.database' => 'database/database.sqlite',
        'backup.backup.destination.disks' => ['local'],
        'backup.backup.password' => null,
    ]);

    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is not ready:')
        ->expectsOutputToContain('APP_DEBUG must be false.')
        ->expectsOutputToContain('APP_URL must use the canonical HTTPS URL.')
        ->expectsOutputToContain('ADMIN_EMAIL must use a private production address.')
        ->expectsOutputToContain('SESSION_SECURE_COOKIE must be true.')
        ->expectsOutputToContain('QUEUE_CONNECTION must use an asynchronous driver.')
        ->expectsOutputToContain('DB_DATABASE must be an absolute path.')
        ->expectsOutputToContain('BACKUP_DISKS must include an off-server disk.')
        ->expectsOutputToContain('BACKUP_ARCHIVE_PASSWORD must be configured.')
        ->doesntExpectOutput('admin@example.test')
        ->assertFailed();
});
