<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use App\Services\ResponsiveImageWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('projects:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate responsive WebP variants for existing project images')]
class GenerateProjectImageVariants extends Command implements Isolatable
{
    protected $isolated = true;

    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed] = $workflow->generate(
            Project::class,
            'featured_image_path',
            'project',
            (bool) $this->option('force'),
            $images,
            function (string $message): void {
                $this->warn($message);
            },
        );

        $noun = $generated === 1 ? 'project' : 'projects';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'project' : 'projects';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
