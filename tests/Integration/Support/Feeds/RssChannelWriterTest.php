<?php

use App\Support\Feeds\RssChannelWriter;
use Illuminate\Support\Carbon;

use function Pest\Laravel\travelTo;

it('writes an escaped channel with one element per line and dates from the newest item', function () {
    travelTo('2026-10-07 12:00:00');

    $xml = new RssChannelWriter()
        ->write(
            title: 'Site & <Co>',
            link: 'https://example.test/?a=1&b=2',
            description: 'Notes on <code>',
            feedUrl: 'https://example.test/rss?x=1&y=2',
            items: [
                [
                    'title' => 'First & "best"',
                    'link' => 'https://example.test/first?a=1&b=2',
                    'description' => '<p>Intro</p>',
                    'publishedAt' => Carbon::parse('2026-10-05 08:30:00'),
                    'category' => 'Arch & Design',
                ],
                [
                    'title' => 'Second',
                    'link' => 'https://example.test/second',
                    'description' => null,
                    'publishedAt' => null,
                ],
            ],
        );

    expect($xml)->toBe(implode("\n", [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
        '<channel>',
        '<title>Site &amp; &lt;Co&gt;</title>',
        '<link>https://example.test/?a=1&amp;b=2</link>',
        '<description>Notes on &lt;code&gt;</description>',
        '<language>en-us</language>',
        '<lastBuildDate>Mon, 05 Oct 2026 08:30:00 +0000</lastBuildDate>',
        '<atom:link href="https://example.test/rss?x=1&amp;y=2" rel="self" type="application/rss+xml" />',
        '<item>',
        '<title>First &amp; "best"</title>',
        '<link>https://example.test/first?a=1&amp;b=2</link>',
        '<guid isPermaLink="true">https://example.test/first?a=1&amp;b=2</guid>',
        '<description>&lt;p&gt;Intro&lt;/p&gt;</description>',
        '<pubDate>Mon, 05 Oct 2026 08:30:00 +0000</pubDate>',
        '<category>Arch &amp; Design</category>',
        '</item>',
        '<item>',
        '<title>Second</title>',
        '<link>https://example.test/second</link>',
        '<guid isPermaLink="true">https://example.test/second</guid>',
        '<description></description>',
        '<pubDate>Wed, 07 Oct 2026 12:00:00 +0000</pubDate>',
        '</item>',
        '</channel>',
        '</rss>',
    ]));
});

it('falls back to the current time for the build date of an empty channel', function () {
    travelTo('2026-10-07 12:00:00');

    $xml = new RssChannelWriter()
        ->write(title: 'Empty', link: 'https://example.test', description: 'Nothing yet', feedUrl: 'https://example.test/rss', items: []);

    expect($xml)
        ->toContain('<lastBuildDate>Wed, 07 Oct 2026 12:00:00 +0000</lastBuildDate>')
        ->toEndWith("<atom:link href=\"https://example.test/rss\" rel=\"self\" type=\"application/rss+xml\" />\n</channel>\n</rss>")
        ->not->toContain('<item>');
});
