<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use App\Services\ResponsiveImageWorkflow;
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

    public function handle(ResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed] = $workflow->generate(
            Post::class,
            'featured_image_path',
            'post',
            (bool) $this->option('force'),
            $images,
            function (string $message): void {
                $this->warn($message);
            },
        );

        $noun = $generated === 1 ? 'post' : 'posts';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'post' : 'posts';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
