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

it('leaves draft and scheduled issues out of the feed', function () {
    // Arrange
    NewsletterIssue::factory()
        ->published()
        ->create(['title' => 'Live entry']);
    NewsletterIssue::factory()->create(['title' => 'Draft entry']);
    NewsletterIssue::factory()
        ->scheduled()
        ->create(['title' => 'Scheduled entry']);

    // Act
    $titles = app(NewsletterRssFeedQuery::class)
        ->get()
        ->pluck('title');

    // Assert
    expect($titles->all())->toBe(['Live entry']);
});
