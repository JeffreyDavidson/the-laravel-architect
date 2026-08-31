<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ResponsiveImageVariants;
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

    public function handle(ResponsiveImageVariants $images): int
    {
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        Project::query()
            ->whereNotNull('featured_image_path')
            ->select(['id', 'featured_image_path'])
            ->eachById(function (Project $project) use ($images, &$generated, &$skipped, &$failed): void {
                $sourcePath = $project->featured_image_path;

                if (! $this->option('force')
                    && is_string($sourcePath)
                    && $images->hasRequiredVariants($sourcePath)) {
                    $skipped++;

                    return;
                }

                if (is_string($sourcePath) && $images->generate($sourcePath)) {
                    $generated++;

                    return;
                }

                $failed++;
                $this->warn("Skipped project {$project->id}: its source image is missing or unsupported.");
            });

        $noun = $generated === 1 ? 'project' : 'projects';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'project' : 'projects';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
