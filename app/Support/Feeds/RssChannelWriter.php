<?php

declare(strict_types=1);

namespace App\Support\Feeds;

use Carbon\CarbonInterface;

/**
 * Writes an RSS 2.0 document with one channel, one element per line. Every text
 * value is XML-escaped, and a missing publication date falls back to the current time.
 */
final class RssChannelWriter
{
    /**
     * The channel's last build date is the first item's publication date, so pass the
     * items newest first.
     *
     * @param  array<int, array{title: string, link: string, description: string|null, publishedAt: CarbonInterface|null, category?: string|null}>  $items
     */
    public function write(string $title, string $link, string $description, string $feedUrl, array $items): string
    {
        return implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
            '<channel>',
            "<title>{$this->escape($title)}</title>",
            "<link>{$this->escape($link)}</link>",
            "<description>{$this->escape($description)}</description>",
            '<language>en-us</language>',
            "<lastBuildDate>{$this->rssDate(array_first($items)['publishedAt'] ?? null)}</lastBuildDate>",
            "<atom:link href=\"{$this->escape($feedUrl)}\" rel=\"self\" type=\"application/rss+xml\" />",
            ...array_merge(...array_map($this->itemLines(...), $items)),
            '</channel>',
            '</rss>',
        ]);
    }

    /**
     * @param  array{title: string, link: string, description: string|null, publishedAt: CarbonInterface|null, category?: string|null}  $item
     * @return list<string>
     */
    private function itemLines(array $item): array
    {
        $link = $this->escape($item['link']);
        $category = $item['category'] ?? null;

        return [
            '<item>',
            "<title>{$this->escape($item['title'])}</title>",
            "<link>{$link}</link>",
            "<guid isPermaLink=\"true\">{$link}</guid>",
            "<description>{$this->escape($item['description'] ?? '')}</description>",
            "<pubDate>{$this->rssDate($item['publishedAt'])}</pubDate>",
            ...($category === null ? [] : ["<category>{$this->escape($category)}</category>"]),
            '</item>',
        ];
    }

    private function rssDate(?CarbonInterface $date): string
    {
        return $date?->toRssString() ?? now()->toRssString();
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}
