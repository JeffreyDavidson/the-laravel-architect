<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\FeaturedImageGenerator;
use App\Services\PostImageGenerationWorkflow;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:generate-images {--force : Regenerate all images}')]
#[Description('Generate missing featured images for posts')]
class GenerateMissingPostImages extends Command
{
    public function handle(
        FeaturedImageGenerator $generator,
        PostImageGenerationWorkflow $workflow,
        ResponsiveImageVariants $images,
    ): int {
        ['processed' => $processed, 'failed' => $failed] = $workflow->generate(
            (bool) $this->option('force'),
            $generator->generate(...),
            $images->generate(...),
            function (string $message): void {
                if (str_starts_with($message, 'Responsive')) {
                    $this->error($message);

                    return;
                }

                $this->info($message);
            },
        );

        if ($failed) {
            return self::FAILURE;
        }

        if ($processed === 0) {
            $this->info('No posts need images generated.');

            return self::SUCCESS;
        }

        $this->info("Done! Generated images for {$processed} posts.");

        return self::SUCCESS;
    }
}
