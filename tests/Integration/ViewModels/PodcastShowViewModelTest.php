<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use RalphJSmit\Laravel\SEO\Support\SEOData;

uses(RefreshDatabase::class);

it('builds a page-aware podcast payload', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about maintainable Laravel applications.',
        'is_active' => true,
    ]);

    foreach (range(1, 21) as $index) {
        Episode::query()->create([
            'podcast_id' => $podcast->getKey(),
            'title' => "Architecture Session {$index}",
            'slug' => "architecture-session-{$index}",
            'description' => "A conversation about architecture topic {$index}.",
            'status' => PublishStatus::Published,
            'published_at' => now()->subDays($index),
        ]);
    }

    Episode::query()->create([
        'podcast_id' => $podcast->getKey(),
        'title' => 'Draft Session',
        'slug' => 'draft-session',
        'description' => 'An unpublished conversation.',
        'status' => PublishStatus::Draft,
    ]);

    Paginator::currentPageResolver(fn (): int => 2);

    $data = app(PodcastShowViewModel::class)
        ->data($podcast);

    $canonicalUrl = route('podcast.show', ['podcast' => $podcast, 'page' => 2]);

    expect($data)->toHaveKeys(['podcast', 'episodes', 'latestEpisode', 'seoSource'])
        ->and($data['podcast']->is($podcast))->toBeTrue()
        ->and($data['episodes']->currentPage())->toBe(2)
        ->and($data['episodes']->total())->toBe(21)
        ->and($data['episodes']->modelKeys())->toBe([
            Episode::query()->where('slug', 'architecture-session-21')->value('id'),
        ])
        ->and($data['episodes']->every(
            fn (Episode $episode): bool => $episode->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['latestEpisode'])->toBeNull()
        ->and($data['seoSource'])->toBeInstanceOf(SEOData::class)
        ->and($data['seoSource']->title)->toBe('Architecture Sessions — Page 2')
        ->and($data['seoSource']->description)->toBe(
            'Conversations about maintainable Laravel applications. Page 2 of 2.',
        )
        ->and($data['seoSource']->url)->toBe($canonicalUrl)
        ->and($data['seoSource']->canonical_url)->toBe($canonicalUrl);
});
