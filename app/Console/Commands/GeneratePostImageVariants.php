<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('posts:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate responsive WebP variants for existing post images')]
class GeneratePostImageVariants extends Command implements Isolatable
{
    protected $isolated = true;

    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageVariants $images): int
    {
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        Post::query()
            ->whereNotNull('featured_image_path')
            ->select(['id', 'featured_image_path'])
            ->eachById(function (Post $post) use ($images, &$generated, &$skipped, &$failed): void {
                $sourcePath = $post->featured_image_path;

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
                $this->warn("Skipped post {$post->id}: its source image is missing or unsupported.");
            });

        $noun = $generated === 1 ? 'post' : 'posts';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'post' : 'posts';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
