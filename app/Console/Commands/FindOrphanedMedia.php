<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StoredMediaOrphanWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('media:find-orphans {--delete : Delete unreferenced files older than 24 hours within managed media directories}')]
#[Description('Find unreferenced files on the public media disk')]
class FindOrphanedMedia extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(StoredMediaOrphanWorkflow $workflow): int
    {
        $delete = (bool) $this->option('delete');
        $report = $workflow->audit($delete);

        foreach ($report['files'] as $file) {
            $status = $delete
                ? ($file['deleted'] ? 'Deleted' : 'Retained')
                : 'Orphaned';

            $this->line("{$status}: {$file['path']} (".$this->formatBytes($file['size']).')');
        }

        $this->info("Found {$report['orphaned']} orphaned files (".$this->formatBytes($report['orphanedBytes']).').');

        if ($report['missing'] > 0) {
            $this->warn("Missing referenced files: {$report['missing']}.");
        }

        if (! $delete) {
            $this->line('No files were deleted. Pass --delete to remove eligible orphans in managed directories after a 24-hour grace period.');

            if ($report['orphaned'] > 0 || $report['missing'] > 0) {
                $this->error('Media storage requires review.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        $this->info("Deleted {$report['deleted']} orphaned files.");

        if ($report['skipped'] > 0) {
            $this->warn("Retained {$report['skipped']} files requiring review or a longer grace period.");
        }

        if ($report['failed'] > 0 || $report['missing'] > 0 || $report['skipped'] > 0) {
            if ($report['failed'] > 0) {
                $this->error("Failed to delete {$report['failed']} orphaned files.");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $value = (float) $bytes;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        $precision = $unit === 0 ? 0 : 1;

        return number_format($value, $precision).' '.$units[$unit];
    }
}
