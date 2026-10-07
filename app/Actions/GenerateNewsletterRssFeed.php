<?php

namespace App\Actions;

use App\Models\NewsletterIssue;
use App\Support\Feeds\RssChannelWriter;

final readonly class GenerateNewsletterRssFeed
{
    public function __construct(private RssChannelWriter $writer) {}

    public function handle(): string
    {
        $issues = NewsletterIssue::query()->published()
            ->latest('published_at')
            ->latest('id')
            ->take(20)
            ->get();

        return $this->writer->write(
            title: 'The Laravel Architect Newsletter',
            link: url('/newsletter'),
            description: 'Practical Laravel architecture notes, tutorials, and updates from The Laravel Architect.',
            feedUrl: route('newsletter.rss'),
            items: $issues
                ->map(fn (NewsletterIssue $issue): array => [
                    'title' => $issue->title,
                    'link' => route('newsletter.issue', $issue),
                    'description' => $issue->excerpt,
                    'publishedAt' => $issue->publishedAt(),
                ])
                ->all(),
        );
    }
}
