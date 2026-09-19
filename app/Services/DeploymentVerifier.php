<?php

namespace App\Services;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Support\Monitoring\Health\NightwatchHealthMonitor;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Process;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

final readonly class DeploymentVerifier
{
    public function __construct(
        private Migrator $migrator,
        private RuntimeHealthMonitor $runtimeHealthMonitor,
        private NightwatchHealthMonitor $nightwatchHealthMonitor,
        private ResponsiveImageWorkflow $responsiveImageWorkflow,
        private ResponsiveImageVariants $responsiveImageVariants,
    ) {}

    /**
     * @return list<string>
     */
    public function failures(string $expectedCommit): array
    {
        return array_values(array_filter([
            $this->commitFailure($expectedCommit),
            $this->migrationFailure(),
            $this->runtimeFailure(),
            $this->nightwatchDeploymentFailure($expectedCommit),
            $this->nightwatchFailure(),
            $this->backupFailure(),
            $this->responsiveMediaFailure(),
        ]));
    }

    private function commitFailure(string $expectedCommit): ?string
    {
        $result = Process::run(['git', 'rev-parse', 'HEAD']);

        if (! $result->successful()) {
            return 'The deployed Git commit could not be read.';
        }

        return hash_equals($expectedCommit, trim($result->output()))
            ? null
            : 'The deployed Git commit does not match the expected release.';
    }

    private function migrationFailure(): ?string
    {
        $files = $this->migrator->getMigrationFiles(database_path('migrations'));
        $ran = $this->migrator->getRepository()->getRan();

        return array_diff(array_keys($files), $ran) === []
            ? null
            : 'The application has pending database migrations.';
    }

    private function runtimeFailure(): ?string
    {
        try {
            $this->runtimeHealthMonitor->ensureHealthy();
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
                || ! $newestBackup instanceof Backup
                || $newestBackup->date()->lt(now()->subHours($maxAge))) {
                return 'One or more backup destinations do not contain a fresh backup.';
            }
        }

        return null;
    }

    private function nightwatchFailure(): ?string
    {
        try {
            $this->nightwatchHealthMonitor->ensureHealthy();
        } catch (\RuntimeException) {
            return 'The Nightwatch agent is unavailable.';
        }

        return null;
    }

    private function nightwatchDeploymentFailure(string $expectedCommit): ?string
    {
        $nightwatchDeployment = config('nightwatch.deployment');

        return is_string($nightwatchDeployment) && hash_equals($expectedCommit, $nightwatchDeployment)
            ? null
            : 'Nightwatch is not configured with the expected deployment identifier.';
    }

    private function responsiveMediaFailure(): ?string
    {
        foreach ([
            [Project::class, 'featured_image_path'],
            [Post::class, 'featured_image_path'],
            [Podcast::class, 'cover_image_path'],
        ] as [$modelClass, $pathColumn]) {
            if (($this->responsiveImageWorkflow->verify($modelClass, $pathColumn, $this->responsiveImageVariants)['failed'] ?? 0) > 0) {
                return 'One or more stored images are missing required responsive variants.';
            }
        }

        return null;
    }
}
