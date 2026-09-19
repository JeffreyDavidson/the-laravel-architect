<?php

namespace App\Services;

use App\Models\Post;

final class PostImageGenerationWorkflow
{
    /**
     * @param  callable(Post): string  $generate
     * @param  callable(string): bool  $generateVariants
     * @param  callable(string): void  $report
     * @return array{processed: int, failed: bool}
     */
    public function generate(
        bool $force,
        callable $generate,
        callable $generateVariants,
        callable $report,
    ): array {
        $processed = 0;
        $failed = false;

        $query = Post::with('category');

        if (! $force) {
            $query->whereNull('featured_image_path');
        }

        $query->eachById(function (Post $post) use ($generate, $generateVariants, $report, &$processed, &$failed): ?bool {
            $processed++;
            $filename = $generate($post);
            $post->update(['featured_image_path' => $filename]);

            if (! $post->wasChanged('featured_image_path') && ! $generateVariants($filename)) {
                $report('Responsive post image regeneration failed.');
                $failed = true;

                return false;
            }

            $report("Generated: {$filename}");

            return null;
        });

        return [
            'processed' => $processed,
            'failed' => $failed,
        ];
    }
}
