<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

final class PostImageGenerationWorkflow
{
    /**
     * Generate featured images for posts without one. Forcing also regenerates images this
     * workflow generated before (under featured-images/), but never replaces an uploaded
     * image, because replacing it deletes the upload and its variants.
     *
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

        $query = Post::query()->with('category')
            ->where(function (Builder $query) use ($force): void {
                $query->whereNull('featured_image_path');

                if ($force) {
                    $query->orWhereLike('featured_image_path', 'featured-images/%');
                }
            });

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
