<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupArchiveVerifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verify-backup')]
#[Description('Restore the newest backup archive in isolation and verify it against the live database and media')]
class VerifyLatestBackup extends Command
{
    public function handle(BackupArchiveVerifier $verifier): int
    {
        $results = $verifier->verifyAll();

        if ($results === []) {
            $this->error('No backup destinations are configured.');

            return self::FAILURE;
        }

        $allPassed = true;

        foreach ($results as $result) {
            $disk = $result->disk;
            $archive = $result->archive ?? 'no archive';
            $this->line("Destination {$disk}: {$archive}");

            foreach ($result->checks as $check) {
                $this->line(" ✓ {$check}");
            }

            foreach ($result->failures as $failure) {
                $this->error(" ✗ {$failure}");
            }

            $allPassed = $allPassed && $result->passed();
        }

        if (! $allPassed) {
            $this->error('The backup could not be verified. Do not rely on it for a release.');

            return self::FAILURE;
        }

        $this->info('The backup is verified and restorable.');

        return self::SUCCESS;
    }
}
