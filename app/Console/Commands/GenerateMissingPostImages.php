<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PostImageGenerationWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:generate-images {--force : Also regenerate previously generated images (uploaded images are kept)}')]
#[Description('Generate missing featured images for posts')]
final class GenerateMissingPostImages extends Command
{
    public function handle(PostImageGenerationWorkflow $workflow): int
    {
        ['processed' => $processed, 'failed' => $failed] = $workflow->generate(
            (bool) $this->option('force'),
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
