<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentArchive\PublicContentImportGuard;
use App\Services\ProductionContentSyncWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('content:sync-production {--staging : Permit synchronization on the staging hostname when APP_ENV is production}')]
#[Description('Replace staging public content and media with the current production versions')]
final class SyncProductionContent extends Command
{
    public function handle(PublicContentImportGuard $guard, ProductionContentSyncWorkflow $workflow): int
    {
        if (! $guard->allows((bool) $this->option('staging'))) {
            $this->error('Production content sync may only run in a non-production environment or explicitly approved staging hostname.');

            return self::FAILURE;
        }

        try {
            ['counts' => $counts, 'mediaCount' => $mediaCount] = $workflow->synchronize();

            $this->info(collect($counts)->map(fn (int $count, string $type): string => "{$count} {$type}")
                ->join(', ').' synchronized.');
            $this->info("{$mediaCount} referenced public media files synchronized.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
