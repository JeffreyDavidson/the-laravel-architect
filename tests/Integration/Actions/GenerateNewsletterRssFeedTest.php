<?php

use App\Actions\GenerateNewsletterRssFeed;
use App\Enums\PublishStatus;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('generates a newest-first feed bounded to twenty published issues', function () {
    foreach (range(1, 21) as $position) {
        NewsletterIssue::query()->create([
            'title' => "Newsletter issue {$position}",
            'slug' => "newsletter-issue-{$position}",
            'content' => "Newsletter content {$position}.",
            'status' => PublishStatus::Published,
            'published_at' => now()->subMinutes($position),
        ]);
    }

    $xml = app(GenerateNewsletterRssFeed::class)->handle();

    expect($xml)
        ->toContain('<title>Newsletter issue 1</title>')
        ->not->toContain('<title>Newsletter issue 21</title>')
        ->and(substr_count($xml, '<item>'))
        ->toBe(20);
});
