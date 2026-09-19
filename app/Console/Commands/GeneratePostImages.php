<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use App\Services\ResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:generate-images {--force : Regenerate all images}')]
#[Description('Generate featured images for posts that don\'t have one')]
class GeneratePostImages extends Command
{
    public function handle(FeaturedImageGenerator $generator, ResponsiveImageVariants $images): int
    {
        $query = Post::with('category');

        if (! $this->option('force')) {
            $query->whereNull('featured_image_path');
        }

        $processed = 0;
        $failed = false;

        $query->eachById(function (Post $post) use ($generator, $images, &$processed, &$failed): ?bool {
            $processed++;
            $filename = $generator->generate($post);
            $post->update(['featured_image_path' => $filename]);
            if (! $post->wasChanged('featured_image_path') && ! $images->generate($filename)) {
                $this->error('Responsive post image regeneration failed.');
                $failed = true;

                return false;
            }
            $this->info("Generated: {$filename}");

            return null;
        });

        if ($failed) {
            return self::FAILURE;
        }

        if ($processed === 0) {
            $this->info('No posts need images generated.');

            return 0;
        }

        $this->info("Done! Generated images for {$processed} posts.");

        return 0;
    }
}
