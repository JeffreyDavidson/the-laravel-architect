<?php

use App\Actions\GenerateNewsletterRssFeed;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('generates a newest-first feed bounded to twenty published issues', function () {
    foreach (range(1, 21) as $position) {
        NewsletterIssue::factory()
            ->published()
            ->create([
                'title' => "Newsletter issue {$position}",
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
