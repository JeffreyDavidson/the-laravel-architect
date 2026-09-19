<?php

namespace App\Services;

use App\Support\Monitoring\Health\NightwatchHealthMonitor;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Process;

final readonly class DeploymentVerifier
{
    public function __construct(
        private Migrator $migrator,
        private RuntimeHealthMonitor $runtimeHealthMonitor,
        private NightwatchHealthMonitor $nightwatchHealthMonitor,
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
        $migrator = $this->migrator;
        $migrationFiles = $migrator->getMigrationFiles(database_path('migrations'));
        $migrationRepository = $migrator->getRepository();
        $ran = $migrationRepository->getRan();

        return array_diff(array_keys($migrationFiles), $ran) === []
            ? null
            : 'The application has pending database migrations.';
    }

    private function runtimeFailure(): ?string
    {
        try {
            $runtimeHealthMonitor = $this->runtimeHealthMonitor;
            $runtimeHealthMonitor->ensureHealthy();
        } catch (\RuntimeException) {
            return 'The scheduler or queue worker heartbeat is stale.';
        }

        return null;
    }

    private function nightwatchFailure(): ?string
    {
        try {
            $nightwatchHealthMonitor = $this->nightwatchHealthMonitor;
            $nightwatchHealthMonitor->ensureHealthy();
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
}
