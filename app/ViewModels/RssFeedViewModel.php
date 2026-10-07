<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use App\Queries\RssFeedQuery;
use Carbon\CarbonInterface;

final readonly class RssFeedViewModel
{
    public function __construct(private RssFeedQuery $query) {}

    /**
     * The site feed's channel and its newest published posts.
     *
     * @return array{title: string, link: string, description: string, feedUrl: string, items: array<int, array{title: string, link: string, description: string|null, publishedAt: CarbonInterface|null, category: string|null}>}
     */
    public function data(): array
    {
        return [
            'title' => config()->string('seo.site_name'),
            'link' => url('/'),
            'description' => config()->string('seo.feed_description'),
            'feedUrl' => route('rss'),
            'items' => $this->query->get()
                ->map(fn (Post $post): array => [
                    'title' => $post->title,
                    'link' => route('blog.show', $post),
                    'description' => $post->excerpt,
                    'publishedAt' => $post->publishedAt(),
                    'category' => $post->category?->name,
                ])
                ->all(),
        ];
    }
}
