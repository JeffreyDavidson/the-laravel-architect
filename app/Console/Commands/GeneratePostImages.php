<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('posts:generate-images {--force : Regenerate all images}')]
#[Description('Generate featured images for posts that don\'t have one')]
class GeneratePostImages extends Command
{
    public function handle(FeaturedImageGenerator $generator): int
    {
        $query = Post::with('category');

        if (! $this->option('force')) {
            $query->whereNull('featured_image_path');
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->info('No posts need images generated.');

            return 0;
        }

        foreach ($posts as $post) {
            $filename = $generator->generate($post);
            $post->update(['featured_image_path' => $filename]);
            $this->info("Generated: {$filename}");
        }

        $this->info("Done! Generated images for {$posts->count()} posts.");

        return 0;
    }
}
