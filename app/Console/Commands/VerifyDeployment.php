<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DeploymentVerifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verify-deployment {commit : Expected deployed Git commit SHA}')]
#[Description('Verify the deployed commit, migrations, monitoring, runtime heartbeats, and backup freshness')]
class VerifyDeployment extends Command
{
    public function handle(DeploymentVerifier $verifier): int
    {
        $failures = $verifier->failures(trim((string) $this->argument('commit')));

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
}
