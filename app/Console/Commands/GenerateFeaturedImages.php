<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use Illuminate\Console\Command;

class GenerateFeaturedImages extends Command
{
    protected $signature = 'posts:generate-images {--force : Regenerate even if image exists}';
    protected $description = 'Generate featured images for posts that don\'t have one';

    public function handle(): int
    {
        $generator = new FeaturedImageGenerator();

        $posts = Post::with('category')->get();
        $generated = 0;

        foreach ($posts as $post) {
            if ($post->featured_image && !$this->option('force')) {
                $this->line("  Skipping: {$post->title} (already has image)");
                continue;
            }

            $this->info("  Generating: {$post->title}");

            try {
                $path = $generator->generate($post);
                $post->update(['featured_image' => $path]);
                $generated++;
                $this->info("  ✓ Saved: {$path}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Generated {$generated} featured images.");

        return self::SUCCESS;
    }
}
