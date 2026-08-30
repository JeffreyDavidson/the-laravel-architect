<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('projects:generate-image-variants')]
#[Description('Generate responsive WebP variants for existing project images')]
class GenerateProjectImageVariants extends Command
{
    public function handle(ResponsiveImageVariants $images): int
    {
        $generated = 0;
        $failed = 0;

        Project::query()
            ->whereNotNull('featured_image_path')
            ->select(['id', 'featured_image_path'])
            ->eachById(function (Project $project) use ($images, &$generated, &$failed): void {
                $sourcePath = $project->featured_image_path;

                if (is_string($sourcePath) && $images->generate($sourcePath)) {
                    $generated++;

                    return;
                }

                $failed++;
                $this->warn("Skipped project {$project->id}: its source image is missing or unsupported.");
            });

        $noun = $generated === 1 ? 'project' : 'projects';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
