<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ScaleTestContentWorkflow;
use App\Support\Content\Archives\PublicContentImportGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('content:scale-test {action : seed or clear generated scale-test records} {--staging : Permit the exact staging hostname when APP_ENV is production}')]
#[Description('Seed or remove synthetic scale-test content in a non-production environment')]
class ManageScaleTestContent extends Command
{
    public function handle(ScaleTestContentWorkflow $workflow, PublicContentImportGuard $guard): int
    {
        $action = $this->argument('action');

        if (! is_string($action) || ! in_array($action, ['seed', 'clear'], true)) {
            $this->error('The action must be either seed or clear.');

            return self::INVALID;
        }

        $staging = (bool) $this->option('staging');

        if (! $guard->allows($staging)) {
            $this->error('Scale-test content may only be changed outside production.');

            return self::FAILURE;
        }

        if ($action === 'seed') {
            $counts = $workflow->seed();
        } else {
            $counts = $workflow->clear();
        }
        $summary = collect($counts)
            ->map(fn (int $count, string $type): string => "{$count} {$type}")
            ->join(', ');

        $this->info("Scale-test content {$action} completed: {$summary}.");

        return self::SUCCESS;
    }
}
