<?php

namespace App\Console\Commands;

use App\Support\RuntimeHealth;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Process;
use Spatie\Backup\BackupDestination\BackupDestination;

#[Signature('app:verify-deployment {commit : Expected deployed Git commit SHA}')]
#[Description('Verify the deployed commit, migrations, runtime heartbeats, and backup freshness')]
class VerifyDeployment extends Command
{
    public function __construct(
        private readonly Migrator $migrator,
        private readonly RuntimeHealth $runtimeHealth,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $failures = array_filter([
            $this->commitFailure(),
            $this->migrationFailure(),
            $this->runtimeFailure(),
            $this->backupFailure(),
            $this->responsiveMediaFailure(),
        ]);

        if ($failures !== []) {
            $this->error('Deployment verification failed:');

            foreach ($failures as $failure) {
                $this->line(" - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info('Deployment verification passed.');

        return self::SUCCESS;
    }

    private function commitFailure(): ?string
    {
        $expectedCommit = trim((string) $this->argument('commit'));
        $result = Process::run(['git', 'rev-parse', 'HEAD']);

        if (! $result->successful()) {
            return 'The deployed Git commit could not be read.';
        }

        $deployedCommit = trim($result->output());

        return hash_equals($expectedCommit, $deployedCommit)
            ? null
            : 'The deployed Git commit does not match the expected release.';
    }

    private function migrationFailure(): ?string
    {
        $files = $this->migrator->getMigrationFiles(database_path('migrations'));
        $ran = $this->migrator->getRepository()->getRan();
        $pending = array_diff(array_keys($files), $ran);

        return $pending === []
            ? null
            : 'The application has pending database migrations.';
    }

    private function runtimeFailure(): ?string
    {
        try {
            $this->runtimeHealth->ensureHealthy();
        } catch (\RuntimeException) {
            return 'The scheduler or queue worker heartbeat is stale.';
        }

        return null;
    }

    private function backupFailure(): ?string
    {
        $name = config('backup.backup.name');
        $disks = config('backup.backup.destination.disks');
        $maxAge = config('health.backup.max_age_hours');

        if (! is_string($name) || ! is_array($disks) || ! is_int($maxAge) || $maxAge < 1) {
            return 'Backup freshness configuration is invalid.';
        }

        foreach ($disks as $disk) {
            if (! is_string($disk)) {
                return 'Backup freshness configuration is invalid.';
            }

            $destination = BackupDestination::create($disk, $name);
            $newestBackup = $destination->newestBackup();

            if (! $destination->isReachable()
                || $newestBackup === null
                || $newestBackup->date()->lt(now()->subHours($maxAge))) {
                return 'One or more backup destinations do not contain a fresh backup.';
            }
        }

        return null;
    }

    private function responsiveMediaFailure(): ?string
    {
        return $this->callSilent('media:verify-responsive-images') === self::SUCCESS
            ? null
            : 'One or more stored images are missing required responsive variants.';
    }
}
