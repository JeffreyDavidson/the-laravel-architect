<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ResponsiveImageRepairWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('media:repair-responsive-images {--force : Regenerate variants that already pass verification}')]
#[Description('Repair responsive image variants for stored project, post, and podcast media')]
class RepairResponsiveImages extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageRepairWorkflow $workflow): int
    {
        $report = $workflow->repair((bool) $this->option('force'));

        foreach ($report['warnings'] as $warning) {
            $this->warn($warning);
        }

        foreach ($report['generations'] as $label => $result) {
            $noun = $result['generated'] === 1 ? $label : "{$label}s";
            $this->info("Generated responsive images for {$result['generated']} {$noun}.");

            if ($result['skipped'] > 0) {
                $skippedNoun = $result['skipped'] === 1 ? $label : "{$label}s";
                $this->line("Skipped {$result['skipped']} already verified {$skippedNoun}.");
            }
        }

        $failures = 0;

        foreach ($report['verification'] as $label => $result) {
            $verified = $result['checked'] - $result['failed'];
            $this->line(ucfirst($label).": {$result['checked']} checked, {$verified} verified, {$result['failed']} failed.");
            $failures += $result['failed'];
        }

        if ($failures > 0) {
            $this->error('Responsive image verification failed.');
        } else {
            $this->info('Responsive image verification passed.');
        }

        $successful = $failures === 0
            && array_all($report['generations'], fn (array $result): bool => $result['failed'] === 0);

        if (! $successful) {
            $this->error('Responsive image repair completed with failures.');

            return self::FAILURE;
        }

        $this->info('Responsive image repair completed successfully.');

        return self::SUCCESS;
    }
}
