<?php

use App\Actions\GenerateRssFeed;
use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('generates a newest-first feed bounded to twenty published posts', function () {
    $author = User::factory()->create();

    foreach (range(1, 21) as $position) {
        Post::query()->create([
            'title' => "Feed post {$position}",
            'slug' => "feed-post-{$position}",
            'content' => "Feed content {$position}.",
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $xml = app(GenerateRssFeed::class)
        ->handle();

    expect($xml)
        ->toContain('<title>Feed post 1</title>')
        ->not->toContain('<title>Feed post 21</title>')
        ->and(substr_count($xml, '<item>'))
        ->toBe(20);
});

it('takes the channel title and description from config and escapes them', function () {
    config()->set('seo.site_name', 'Site & Co');
    config()->set('seo.feed_description', 'Notes on <code> & more.');

    $xml = app(GenerateRssFeed::class)
        ->handle();

    expect($xml)
        ->toContain('<title>Site &amp; Co</title>')
        ->toContain('<description>Notes on &lt;code&gt; &amp; more.</description>');
});
