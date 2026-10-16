<?php

use App\Enums\ContentReadinessArea;
use App\Models\Episode;
use App\Publishing\ContentReadinessSummaryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

it('counts the incomplete records of every area', function () {
    // Arrange
    Episode::factory()->create();
    $summary = app(ContentReadinessSummaryQuery::class);

    // Act
    $counts = $summary->outstandingCounts();

    // Assert
    expect(array_keys($counts))->toBe(array_column(ContentReadinessArea::cases(), 'value'))
        ->and($counts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(1);
});

it('serves the cached counts within a minute', function () {
    // Arrange
    Episode::factory()->create();
    $summary = app(ContentReadinessSummaryQuery::class);
    $summary->outstandingCounts();
    Episode::factory()->create();

    // Act
    $cachedCounts = $summary->outstandingCounts();

    // Assert
    expect($cachedCounts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(1);
});

it('refreshes the counts once the minute has passed', function () {
    // Arrange
    Episode::factory()->create();
    $summary = app(ContentReadinessSummaryQuery::class);
    $summary->outstandingCounts();
    Episode::factory()->create();
    travel(61)->seconds();

    // Act
    $refreshedCounts = $summary->outstandingCounts();

    // Assert
    expect($refreshedCounts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(2);
});
