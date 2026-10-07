<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ScaleTestContentAction;
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
        $argument = $this->argument('action');
        $action = is_string($argument)
            ? ScaleTestContentAction::tryFrom($argument)
            : null;

        if (! $action instanceof ScaleTestContentAction) {
            $this->error('The action must be either seed or clear.');

            return self::INVALID;
        }

        $staging = (bool) $this->option('staging');

        if (! $guard->allows($staging)) {
            $this->error('Scale-test content may only be changed outside production.');

            return self::FAILURE;
        }

        $counts = match ($action) {
            ScaleTestContentAction::Seed => $workflow->seed(),
            ScaleTestContentAction::Clear => $workflow->clear(),
        };
        $summary = collect($counts)
            ->map(fn (int $count, string $type): string => "{$count} {$type}")
            ->join(', ');

        $this->info("Scale-test content {$action->value} completed: {$summary}.");

        return self::SUCCESS;
    }
}
