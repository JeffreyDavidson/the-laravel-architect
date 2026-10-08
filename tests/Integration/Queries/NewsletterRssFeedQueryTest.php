<?php

use App\Models\NewsletterIssue;
use App\Queries\NewsletterRssFeedQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('returns the twenty newest published issues, newest first', function () {
    foreach (range(1, 21) as $position) {
        NewsletterIssue::factory()
            ->published()
            ->create([
                'title' => "Newsletter issue {$position}",
                'published_at' => now()->subMinutes($position),
            ]);
    }

    $titles = app(NewsletterRssFeedQuery::class)
        ->get()
        ->pluck('title');

    expect($titles)
        ->toHaveCount(20)
        ->first()
        ->toBe('Newsletter issue 1')
        ->and($titles)
        ->not->toContain('Newsletter issue 21');
});
