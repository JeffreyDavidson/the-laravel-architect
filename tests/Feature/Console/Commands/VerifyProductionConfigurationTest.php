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
        'backup.backup.source.files.include' => ['/var/www/the-laravel-architect/shared/storage/app/public'],
        'backup.backup.source.files.exclude' => [base_path('.env')],
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
        'nightwatch.server' => 'production-web-1',
        'nightwatch.sampling.requests' => 0.1,
        'nightwatch.sampling.commands' => 1.0,
        'nightwatch.sampling.exceptions' => 1.0,
        'nightwatch.sampling.scheduled_tasks' => 1.0,
        'nightwatch.capture_request_payload' => false,
        'nightwatch.capture_exception_source_code' => false,
        'nightwatch.filtering.ignore_mail' => true,
        'nightwatch.redact_payload_fields' => ['_token', 'password', 'password_confirmation', 'email', 'name', 'message'],
        'nightwatch.redact_headers' => ['Authorization', 'Cookie', 'Proxy-Authorization', 'X-XSRF-TOKEN', 'X-Forwarded-For', 'X-Real-IP', 'CF-Connecting-IP', 'True-Client-IP'],
        'nightwatch.ingest.uri' => '127.0.0.1:2407',
        'nightwatch.ingest.timeout' => 0.5,
        'nightwatch.ingest.connection_timeout' => 0.5,
        'nightwatch.ingest.event_buffer' => 500,
    ]);
});

it('accepts a safe production configuration', function () {
    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is ready.')
        ->assertSuccessful();
});

it('accepts a lower Nightwatch request sample rate', function () {
    config()->set('nightwatch.sampling.requests', 0.05);

    $this->artisan('app:verify-production')
        ->expectsOutput('Production configuration is ready.')
        ->assertSuccessful();
});

it('rejects an unsafe Nightwatch request sample rate', function (mixed $sampleRate) {
    config()->set('nightwatch.sampling.requests', $sampleRate);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('NIGHTWATCH_REQUEST_SAMPLE_RATE must be greater than zero and no more than 0.1.')
        ->assertFailed();
})->with([
    'disabled' => 0.0,
    'negative' => -0.1,
    'above the ceiling' => 0.11,
    'malformed' => '0.1',
]);

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

it('rejects the application release directory as a backup source', function () {
    config()->set('backup.backup.source.files.include', [base_path()]);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.')
        ->assertFailed();
});

it('rejects a media backup source inside the application release directory', function () {
    config()->set('backup.backup.source.files.include', [storage_path('app/public')]);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.')
        ->assertFailed();
});

it('rejects a relative media backup source', function () {
    config()->set('backup.backup.source.files.include', ['storage/app/public']);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.')
        ->assertFailed();
});

it('requires the production environment file to be excluded from backups', function () {
    config()->set('backup.backup.source.files.exclude', []);

    $this->artisan('app:verify-production')
        ->expectsOutputToContain('BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.')
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
        'backup.backup.source.files.include' => [base_path()],
        'backup.backup.source.files.exclude' => [],
        'backup.backup.destination.disks' => ['local'],
        'backup.backup.password' => null,
        'health.backup.max_age_hours' => 0,
        'health.failed_jobs.alert_threshold' => -1,
        'health.failed_jobs.retention_hours' => 0,
        'health.runtime.enabled' => false,
        'health.runtime.max_age_seconds' => 30,
        'nightwatch.enabled' => false,
        'nightwatch.token' => null,
        'nightwatch.server' => null,
        'nightwatch.sampling.requests' => 1.0,
        'nightwatch.sampling.commands' => 0.0,
        'nightwatch.sampling.exceptions' => 1.1,
        'nightwatch.sampling.scheduled_tasks' => '1.0',
        'nightwatch.capture_request_payload' => true,
        'nightwatch.capture_exception_source_code' => true,
        'nightwatch.filtering.ignore_mail' => false,
        'nightwatch.redact_payload_fields' => ['_token'],
        'nightwatch.redact_headers' => ['Authorization'],
        'nightwatch.ingest.uri' => null,
        'nightwatch.ingest.timeout' => 0.0,
        'nightwatch.ingest.connection_timeout' => -1.0,
        'nightwatch.ingest.event_buffer' => 0,
        'logging.channels.nightwatch.handler' => null,
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
        ->expectsOutputToContain('BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.')
        ->expectsOutputToContain('BACKUP_DISKS must include an off-server disk.')
        ->expectsOutputToContain('BACKUP_ARCHIVE_PASSWORD must be configured.')
        ->expectsOutputToContain('BACKUP_MAX_AGE_HOURS must be at least 1.')
        ->expectsOutputToContain('QUEUE_FAILED_JOB_ALERT_THRESHOLD must be zero or greater.')
        ->expectsOutputToContain('QUEUE_FAILED_JOB_RETENTION_HOURS must be at least 1.')
        ->expectsOutputToContain('RUNTIME_HEALTH_ENABLED must be true.')
        ->expectsOutputToContain('RUNTIME_HEALTH_MAX_AGE must be at least 60 seconds.')
        ->expectsOutputToContain('NIGHTWATCH_ENABLED must be true.')
        ->expectsOutputToContain('NIGHTWATCH_TOKEN must be configured.')
        ->expectsOutputToContain('NIGHTWATCH_SERVER must identify the monitored server.')
        ->expectsOutputToContain('NIGHTWATCH_REQUEST_SAMPLE_RATE must be greater than zero and no more than 0.1.')
        ->expectsOutputToContain('NIGHTWATCH_COMMAND_SAMPLE_RATE must be greater than zero and no more than 1.0.')
        ->expectsOutputToContain('NIGHTWATCH_EXCEPTION_SAMPLE_RATE must be greater than zero and no more than 1.0.')
        ->expectsOutputToContain('NIGHTWATCH_SCHEDULED_TASK_SAMPLE_RATE must be greater than zero and no more than 1.0.')
        ->expectsOutputToContain('NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD must be false.')
        ->expectsOutputToContain('NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE must be false.')
        ->expectsOutputToContain('NIGHTWATCH_IGNORE_MAIL must be true.')
        ->expectsOutputToContain('Nightwatch payload redactions must include every required sensitive field.')
        ->expectsOutputToContain('Nightwatch header redactions must include every required credential and client-address header.')
        ->expectsOutputToContain('NIGHTWATCH_INGEST_URI must be configured.')
        ->expectsOutputToContain('NIGHTWATCH_INGEST_TIMEOUT must be greater than zero.')
        ->expectsOutputToContain('NIGHTWATCH_INGEST_CONNECTION_TIMEOUT must be greater than zero.')
        ->expectsOutputToContain('NIGHTWATCH_INGEST_EVENT_BUFFER must be at least 1.')
        ->expectsOutputToContain('Nightwatch log capture must remain disabled.')
        ->doesntExpectOutput('admin@example.test')
        ->assertFailed();
});
