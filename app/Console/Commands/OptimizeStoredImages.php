<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StoredImageOptimizationWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('media:optimize-images {--dry-run : Report changes without writing files} {--force : Re-optimize existing WebP images}')]
#[Description('Replace stored content images with optimized WebP files')]
class OptimizeStoredImages extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(StoredImageOptimizationWorkflow $workflow): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $report = $workflow->optimizeAll(
            $dryRun,
            $force,
            function (string $message): void {
                $this->warn($message);
            },
        );

        foreach ($report['results'] as $result) {
            $label = $result['label'];

            $noun = $result['optimized'] === 1 ? $label : "{$label}s";
            $verb = $dryRun ? 'Would optimize' : 'Optimized';
            $this->info("{$verb} {$result['optimized']} {$noun}.");

            if ($result['skipped'] > 0) {
                $skippedNoun = $result['skipped'] === 1 ? $label : "{$label}s";
                $this->line("Skipped {$result['skipped']} already optimized {$skippedNoun}.");
            }

        }

        if ($report['failed'] > 0) {
            $this->error('Stored image optimization completed with failures.');

            return self::FAILURE;
        }

        $this->info($dryRun
            ? 'Stored image optimization dry run completed successfully.'
            : 'Stored image optimization completed successfully.');

        return self::SUCCESS;
    }
}
