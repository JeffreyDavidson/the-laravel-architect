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
        'services.turnstile.site_key' => 'production-site-key',
        'services.turnstile.secret_key' => 'production-secret-key',
        'services.turnstile.contact_action' => 'contact-form',
        'services.turnstile.allowed_hostnames' => ['thelaravelarchitect.com', 'www.thelaravelarchitect.com'],
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => '/var/www/the-laravel-architect/database/database.sqlite',
        'backup.backup.destination.disks' => ['local', 's3'],
        'backup.backup.password' => 'encrypted-archive-password',
        'backup.notifications.mail.to' => 'backups@thelaravelarchitect.com',
        'health.backup.max_age_hours' => 36,
        'health.failed_jobs.alert_threshold' => 0,
        'health.failed_jobs.retention_hours' => 168,
        'health.runtime.enabled' => true,
        'health.runtime.max_age_seconds' => 300,
        'nightwatch.enabled' => true,
        'nightwatch.token' => 'production-nightwatch-token',
        'nightwatch.capture_request_payload' => false,
        'nightwatch.capture_exception_source_code' => false,
        'nightwatch.filtering.ignore_mail' => true,
    ]);
});

it('accepts a safe production configuration', function () {
    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is ready.')
        ->assertSuccessful();
});

it('accepts a fully configured NAS backup disk', function () {
    config()->set([
        'backup.backup.destination.disks' => ['local', 'nas-backups'],
        'filesystems.disks.nas-backups' => [
            'driver' => 'sftp',
            'host' => 'nas.internal.test',
            'username' => 'backup-user',
            'password' => 'secret-password',
            'port' => 22,
            'root' => '/backups',
            'hostFingerprint' => 'SHA256:test-fingerprint',
        ],
    ]);

    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is ready.')
        ->assertSuccessful();
});

it('rejects an incomplete NAS backup disk without exposing credentials', function () {
    config()->set([
        'backup.backup.destination.disks' => ['local', 'nas-backups'],
        'filesystems.disks.nas-backups.password' => 'secret-password',
        'filesystems.disks.nas-backups.hostFingerprint' => null,
    ]);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('The nas-backups disk must configure host, username, password, root, port, and host fingerprint.')
        ->doesntExpectOutput('secret-password')
        ->assertFailed();
});

it('rejects an unknown backup disk', function () {
    config()->set('backup.backup.destination.disks', ['local', 'missing-disk']);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('BACKUP_DISKS must reference configured filesystem disks.')
        ->assertFailed();
});

it('reports every unsafe production setting without exposing its value', function () {
    config()->set([
        'app.debug' => true,
        'app.url' => 'http://localhost',
        'app.admin_email' => 'admin@example.test',
        'session.secure' => false,
        'queue.default' => 'sync',
        'services.turnstile.site_key' => null,
        'services.turnstile.secret_key' => null,
        'services.turnstile.contact_action' => null,
        'services.turnstile.allowed_hostnames' => ['attacker.example'],
        'database.connections.sqlite.database' => 'database/database.sqlite',
        'backup.backup.destination.disks' => ['local'],
        'backup.backup.password' => null,
        'health.backup.max_age_hours' => 0,
        'health.failed_jobs.alert_threshold' => -1,
        'health.failed_jobs.retention_hours' => 0,
        'health.runtime.enabled' => false,
        'health.runtime.max_age_seconds' => 30,
        'nightwatch.enabled' => false,
        'nightwatch.token' => null,
        'nightwatch.capture_request_payload' => true,
        'nightwatch.capture_exception_source_code' => true,
        'nightwatch.filtering.ignore_mail' => false,
    ]);

    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is not ready:')
        ->expectsOutputToContain('APP_DEBUG must be false.')
        ->expectsOutputToContain('APP_URL must use the canonical HTTPS URL.')
        ->expectsOutputToContain('ADMIN_EMAIL must use a private production address.')
        ->expectsOutputToContain('SESSION_SECURE_COOKIE must be true.')
        ->expectsOutputToContain('QUEUE_CONNECTION must use an asynchronous driver.')
        ->expectsOutputToContain('TURNSTILE_SITE_KEY must be configured.')
        ->expectsOutputToContain('TURNSTILE_SECRET_KEY must be configured.')
        ->expectsOutputToContain('TURNSTILE_CONTACT_ACTION must be configured.')
        ->expectsOutputToContain('TURNSTILE_ALLOWED_HOSTNAMES must include the APP_URL hostname.')
        ->expectsOutputToContain('DB_DATABASE must be an absolute path.')
        ->expectsOutputToContain('BACKUP_DISKS must include an off-server disk.')
        ->expectsOutputToContain('BACKUP_ARCHIVE_PASSWORD must be configured.')
        ->expectsOutputToContain('BACKUP_MAX_AGE_HOURS must be at least 1.')
        ->expectsOutputToContain('QUEUE_FAILED_JOB_ALERT_THRESHOLD must be zero or greater.')
        ->expectsOutputToContain('QUEUE_FAILED_JOB_RETENTION_HOURS must be at least 1.')
        ->expectsOutputToContain('RUNTIME_HEALTH_ENABLED must be true.')
        ->expectsOutputToContain('RUNTIME_HEALTH_MAX_AGE must be at least 60 seconds.')
        ->expectsOutputToContain('NIGHTWATCH_ENABLED must be true.')
        ->expectsOutputToContain('NIGHTWATCH_TOKEN must be configured.')
        ->expectsOutputToContain('NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD must be false.')
        ->expectsOutputToContain('NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE must be false.')
        ->expectsOutputToContain('NIGHTWATCH_IGNORE_MAIL must be true.')
        ->doesntExpectOutput('admin@example.test')
        ->assertFailed();
});
