<?php

use App\Enums\ContentReadinessArea;
use App\Models\Episode;
use App\Publishing\ContentReadinessSummaryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\travel;

pest()->use(RefreshDatabase::class);

it('counts the incomplete records of every area and caches the counts for a minute', function () {
    Episode::factory()->create();
    $summary = app(ContentReadinessSummaryQuery::class);

    $counts = $summary->outstandingCounts();
    Episode::factory()->create();
    $cachedCounts = $summary->outstandingCounts();
    travel(61)->seconds();
    $refreshedCounts = $summary->outstandingCounts();

    expect(array_keys($counts))->toBe(array_column(ContentReadinessArea::cases(), 'value'))
        ->and($counts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(1)
        ->and($cachedCounts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(1)
        ->and($refreshedCounts[ContentReadinessArea::EpisodeDetails->value])
        ->toBe(2);
});
