<?php

namespace App\Actions;

use App\Models\Post;
use App\Support\Feeds\RssChannelWriter;

final readonly class GenerateRssFeed
{
    public function __construct(private RssChannelWriter $writer) {}

    public function handle(): string
    {
        $posts = Post::query()->published()
            ->latest('published_at')
            ->latest('id')
            ->with('category')
            ->take(20)
            ->get();

        return $this->writer->write(
            title: config()->string('seo.site_name'),
            link: url('/'),
            description: config()->string('seo.feed_description'),
            feedUrl: route('rss'),
            items: $posts
                ->map(fn (Post $post): array => [
                    'title' => $post->title,
                    'link' => route('blog.show', $post),
                    'description' => $post->excerpt,
                    'publishedAt' => $post->publishedAt(),
                    'category' => $post->category?->name,
                ])
                ->all(),
        );
    }
}
