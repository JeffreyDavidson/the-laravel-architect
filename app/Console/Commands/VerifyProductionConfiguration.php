<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductionConfigurationVerifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verify-production')]
#[Description('Verify that required production settings are safely configured')]
class VerifyProductionConfiguration extends Command
{
    public function handle(ProductionConfigurationVerifier $verifier): int
    {
        $failures = $verifier->failures();

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
}
