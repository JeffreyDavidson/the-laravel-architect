<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Monolog\Handler\NullHandler;

#[Signature('app:verify-production')]
#[Description('Verify that required production settings are safely configured')]
class VerifyProductionConfiguration extends Command
{
    private const array REQUIRED_NIGHTWATCH_PAYLOAD_REDACTIONS = [
        '_token',
        'password',
        'password_confirmation',
        'email',
        'name',
        'message',
    ];

    private const array REQUIRED_NIGHTWATCH_HEADER_REDACTIONS = [
        'Authorization',
        'Cookie',
        'Proxy-Authorization',
        'X-XSRF-TOKEN',
        'X-Forwarded-For',
        'X-Real-IP',
        'CF-Connecting-IP',
        'True-Client-IP',
    ];

    public function handle(): int
    {
        $databaseConnection = config('database.default');
        $databaseConfig = is_string($databaseConnection)
            ? config("database.connections.{$databaseConnection}")
            : null;
        $databasePath = is_array($databaseConfig) ? ($databaseConfig['database'] ?? null) : null;
        $backupDisks = config('backup.backup.destination.disks');

        $checks = [
            [config('app.env') === 'production', 'APP_ENV must be production.'],
            [config('app.debug') === false, 'APP_DEBUG must be false.'],
            [$this->usesHttps(config('app.url')), 'APP_URL must use the canonical HTTPS URL.'],
            [$this->isConfigured(config('app.key')), 'APP_KEY must be configured.'],
            [config('session.secure') === true, 'SESSION_SECURE_COOKIE must be true.'],
            [$this->usesAsynchronousQueue(config('queue.default')), 'QUEUE_CONNECTION must use an asynchronous driver.'],
            [$this->usesDeliveringMailer(config('mail.default')), 'MAIL_MAILER must use a delivering transport.'],
            [$this->isProductionEmail(config('app.admin_email')), 'ADMIN_EMAIL must use a private production address.'],
            [$this->isProductionEmail(config('app.content_author_email')), 'CONTENT_AUTHOR_EMAIL must use a private production address.'],
            [$this->isProductionEmail(config('mail.contact_to')), 'MAIL_CONTACT_TO must use a monitored production address.'],
            [$this->isProductionEmail(config('backup.notifications.mail.to')), 'BACKUP_NOTIFICATION_EMAIL must use a monitored production address.'],
            [$this->isConfigured(config('services.turnstile.site_key')), 'TURNSTILE_SITE_KEY must be configured.'],
            [$this->isConfigured(config('services.turnstile.secret_key')), 'TURNSTILE_SECRET_KEY must be configured.'],
            [$this->isConfigured(config('services.turnstile.contact_action')), 'TURNSTILE_CONTACT_ACTION must be configured.'],
            [$this->includesAppHostname(config('services.turnstile.allowed_hostnames'), config('app.url')), 'TURNSTILE_ALLOWED_HOSTNAMES must include the APP_URL hostname.'],
            [is_string($databasePath) && str_starts_with($databasePath, DIRECTORY_SEPARATOR), 'DB_DATABASE must be an absolute path.'],
            [$this->hasSafeBackupFileSources(config('backup.backup.source.files.include'), config('backup.backup.source.files.exclude')), 'BACKUP_MEDIA_PATH must be an absolute persistent path outside the release directory, and .env must be excluded.'],
            [$this->hasOffServerBackup($backupDisks), 'BACKUP_DISKS must include an off-server disk.'],
            [$this->hasKnownBackupDisks($backupDisks), 'BACKUP_DISKS must reference configured filesystem disks.'],
            [$this->hasConfiguredNasBackup($backupDisks), 'The nas-backups disk must configure host, username, password, root, port, and host fingerprint.'],
            [$this->isConfigured(config('backup.backup.password')), 'BACKUP_ARCHIVE_PASSWORD must be configured.'],
            [$this->isPositiveInteger(config('health.backup.max_age_hours')), 'BACKUP_MAX_AGE_HOURS must be at least 1.'],
            [$this->isPositiveInteger(config('health.failed_jobs.retention_hours')), 'QUEUE_FAILED_JOB_RETENTION_HOURS must be at least 1.'],
            [config('health.runtime.enabled') === true, 'RUNTIME_HEALTH_ENABLED must be true.'],
            [$this->hasValidHeartbeatMaxAge(config('health.runtime.max_age_seconds')), 'RUNTIME_HEALTH_MAX_AGE must be at least 60 seconds.'],
            [config('nightwatch.enabled') === true, 'NIGHTWATCH_ENABLED must be true.'],
            [$this->isConfigured(config('nightwatch.token')), 'NIGHTWATCH_TOKEN must be configured.'],
            [$this->isConfigured(config('nightwatch.server')), 'NIGHTWATCH_SERVER must identify the monitored server.'],
            [$this->hasValidNightwatchSampleRate(config('nightwatch.sampling.requests'), 0.1), 'NIGHTWATCH_REQUEST_SAMPLE_RATE must be greater than zero and no more than 0.1.'],
            [$this->hasValidNightwatchSampleRate(config('nightwatch.sampling.commands')), 'NIGHTWATCH_COMMAND_SAMPLE_RATE must be greater than zero and no more than 1.0.'],
            [$this->hasValidNightwatchSampleRate(config('nightwatch.sampling.exceptions')), 'NIGHTWATCH_EXCEPTION_SAMPLE_RATE must be greater than zero and no more than 1.0.'],
            [$this->hasValidNightwatchSampleRate(config('nightwatch.sampling.scheduled_tasks')), 'NIGHTWATCH_SCHEDULED_TASK_SAMPLE_RATE must be greater than zero and no more than 1.0.'],
            [config('nightwatch.capture_request_payload') === false, 'NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD must be false.'],
            [config('nightwatch.capture_exception_source_code') === false, 'NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE must be false.'],
            [config('nightwatch.filtering.ignore_mail') === true, 'NIGHTWATCH_IGNORE_MAIL must be true.'],
            [$this->containsRequiredNightwatchRedactions(config('nightwatch.redact_payload_fields'), self::REQUIRED_NIGHTWATCH_PAYLOAD_REDACTIONS), 'Nightwatch payload redactions must include every required sensitive field.'],
            [$this->containsRequiredNightwatchRedactions(config('nightwatch.redact_headers'), self::REQUIRED_NIGHTWATCH_HEADER_REDACTIONS), 'Nightwatch header redactions must include every required credential and client-address header.'],
            [$this->isConfigured(config('nightwatch.ingest.uri')), 'NIGHTWATCH_INGEST_URI must be configured.'],
            [$this->isPositiveNumber(config('nightwatch.ingest.timeout')), 'NIGHTWATCH_INGEST_TIMEOUT must be greater than zero.'],
            [$this->isPositiveNumber(config('nightwatch.ingest.connection_timeout')), 'NIGHTWATCH_INGEST_CONNECTION_TIMEOUT must be greater than zero.'],
            [$this->isPositiveInteger(config('nightwatch.ingest.event_buffer')), 'NIGHTWATCH_INGEST_EVENT_BUFFER must be at least 1.'],
            [config('logging.channels.nightwatch.handler') === NullHandler::class, 'Nightwatch log capture must remain disabled.'],
        ];

        $failures = array_map(
            fn (array $check): string => $check[1],
            array_filter($checks, fn (array $check): bool => ! $check[0]),
        );

        if ($failures !== []) {
            $this->error('Production configuration is not ready:');

            foreach ($failures as $failure) {
                $this->line(" - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info('Production configuration is ready.');

        return self::SUCCESS;
    }

    private function usesHttps(mixed $url): bool
    {
        return is_string($url)
            && str_starts_with($url, 'https://')
            && is_string(parse_url($url, PHP_URL_HOST));
    }

    private function isConfigured(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function usesAsynchronousQueue(mixed $connection): bool
    {
        return is_string($connection) && ! in_array($connection, ['null', 'sync'], true);
    }

    private function usesDeliveringMailer(mixed $mailer): bool
    {
        return is_string($mailer) && ! in_array($mailer, ['array', 'log'], true);
    }

    private function isProductionEmail(mixed $email): bool
    {
        return is_string($email)
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ! str_ends_with($email, '@example.com')
            && ! str_ends_with($email, '@example.test');
    }

    private function includesAppHostname(mixed $allowedHostnames, mixed $appUrl): bool
    {
        if (! is_array($allowedHostnames) || ! is_string($appUrl)) {
            return false;
        }

        $appHostname = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($appHostname)) {
            return false;
        }

        $normalizedHostnames = array_map(
            fn (mixed $hostname): string => is_string($hostname)
                ? strtolower(rtrim(trim($hostname), '.'))
                : '',
            $allowedHostnames,
        );

        return in_array(strtolower(rtrim($appHostname, '.')), $normalizedHostnames, true);
    }

    private function hasOffServerBackup(mixed $disks): bool
    {
        return is_array($disks) && array_any(
            $disks,
            fn (mixed $disk): bool => is_string($disk) && $disk !== 'local',
        );
    }

    private function hasSafeBackupFileSources(mixed $sources, mixed $excludes): bool
    {
        if (! is_array($sources) || count($sources) !== 1 || ! is_array($excludes)) {
            return false;
        }

        $source = $sources[0] ?? null;

        if (! is_string($source) || ! str_starts_with($source, DIRECTORY_SEPARATOR)) {
            return false;
        }

        $basePath = rtrim(base_path(), DIRECTORY_SEPARATOR);
        $sourcePath = rtrim($source, DIRECTORY_SEPARATOR);
        $excludedPaths = array_map(
            fn (mixed $path): string => is_string($path) ? rtrim($path, DIRECTORY_SEPARATOR) : '',
            $excludes,
        );

        $sourceOverlapsRelease = $sourcePath === $basePath
            || str_starts_with($sourcePath, "{$basePath}".DIRECTORY_SEPARATOR)
            || str_starts_with($basePath, "{$sourcePath}".DIRECTORY_SEPARATOR);

        return ! $sourceOverlapsRelease
            && in_array(base_path('.env'), $excludedPaths, true);
    }

    private function hasKnownBackupDisks(mixed $disks): bool
    {
        return is_array($disks) && array_all(
            $disks,
            fn (mixed $disk): bool => is_string($disk)
                && is_array(config("filesystems.disks.{$disk}")),
        );
    }

    private function hasConfiguredNasBackup(mixed $disks): bool
    {
        if (! is_array($disks) || ! in_array('nas-backups', $disks, true)) {
            return true;
        }

        $disk = config('filesystems.disks.nas-backups');

        if (! is_array($disk)) {
            return false;
        }

        return ($disk['driver'] ?? null) === 'sftp'
            && $this->isConfigured($disk['host'] ?? null)
            && $this->isConfigured($disk['username'] ?? null)
            && $this->isConfigured($disk['password'] ?? null)
            && $this->isConfigured($disk['root'] ?? null)
            && $this->isConfigured($disk['hostFingerprint'] ?? null)
            && $this->isPositiveInteger($disk['port'] ?? null);
    }

    private function hasValidHeartbeatMaxAge(mixed $maxAge): bool
    {
        return is_int($maxAge) && $maxAge >= 60;
    }

    private function hasValidNightwatchSampleRate(mixed $sampleRate, float $maximum = 1.0): bool
    {
        return is_float($sampleRate)
            && $sampleRate > 0
            && $sampleRate <= $maximum;
    }

    /**
     * @param  list<string>  $required
     */
    private function containsRequiredNightwatchRedactions(mixed $configured, array $required): bool
    {
        if (! is_array($configured)) {
            return false;
        }

        $normalized = array_map(
            fn (mixed $value): string => is_string($value) ? strtolower(trim($value)) : '',
            $configured,
        );

        return array_all(
            $required,
            fn (string $value): bool => in_array(strtolower($value), $normalized, true),
        );
    }

    private function isPositiveNumber(mixed $value): bool
    {
        return (is_float($value) || is_int($value)) && $value > 0;
    }

    private function isPositiveInteger(mixed $value): bool
    {
        return is_int($value) && $value >= 1;
    }
}
