<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('podcasts:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate responsive WebP variants for existing podcast cover images')]
class GeneratePodcastImageVariants extends Command implements Isolatable
{
    protected $isolated = true;

    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageVariants $images): int
    {
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        Podcast::query()
            ->whereNotNull('cover_image_path')
            ->select(['id', 'cover_image_path'])
            ->eachById(function (Podcast $podcast) use ($images, &$generated, &$skipped, &$failed): void {
                $sourcePath = $podcast->cover_image_path;

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
                $this->warn("Skipped podcast {$podcast->id}: its source image is missing or unsupported.");
            });

        $noun = $generated === 1 ? 'podcast' : 'podcasts';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'podcast' : 'podcasts';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
