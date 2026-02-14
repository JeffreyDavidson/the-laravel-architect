<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\FeaturedImageGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GeneratePostImages extends Command
{
    protected $signature = 'posts:generate-images {--force : Regenerate all images}';
    protected $description = 'Generate featured images for posts that don\'t have one';

    public function handle(FeaturedImageGenerator $generator): int
    {
        $query = Post::with('category');

        if (!$this->option('force')) {
            $query->whereNull('featured_image');
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->info('No posts need images generated.');
            return 0;
        }

        Storage::disk('public')->makeDirectory('posts');

        foreach ($posts as $post) {
            $image = $generator->generate($post);
            $filename = "posts/{$post->slug}.png";
            Storage::disk('public')->put($filename, $image->toPng()->toString());
            $post->update(['featured_image' => $filename]);
            $this->info("Generated: {$filename}");
        }

        $this->info("Done! Generated images for {$posts->count()} posts.");
        return 0;
    }
}
