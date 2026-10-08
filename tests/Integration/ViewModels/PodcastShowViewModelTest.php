<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\PodcastShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('builds a page-aware podcast payload', function () {
    $podcast = Podcast::factory()->create([
        'name' => 'Architecture Sessions',
        'description' => 'Conversations about maintainable Laravel applications.',
    ]);

    foreach (range(1, 21) as $index) {
        Episode::factory()
            ->for($podcast)
            ->published()
            ->create([
                'slug' => "architecture-session-{$index}",
                'published_at' => now()->subDays($index),
            ]);
    }

    Episode::factory()
        ->for($podcast)
        ->create();

    Paginator::currentPageResolver(fn (): int => 2);

    $data = app(PodcastShowViewModel::class)
        ->data($podcast);

    $canonicalUrl = route('podcast.show', ['podcast' => $podcast, 'page' => 2]);

    expect($data)->toHaveKeys(['podcast', 'episodes', 'latestEpisode', 'pageMeta'])
        ->and($data['podcast']->is($podcast))
        ->toBeTrue()
        ->and($data['episodes']->currentPage())
        ->toBe(2)
        ->and($data['episodes']->total())
        ->toBe(21)
        ->and($data['episodes']->getCollection()
            ->map(fn (Episode $episode) => $episode->getKey())
            ->all())
        ->toBe([
            Episode::query()->where('slug', 'architecture-session-21')
                ->value('id'),
        ])
        ->and($data['episodes']->every(
            fn (Episode $episode): bool => $episode->relationLoaded('tags'),
        ))->toBeTrue()
        ->and($data['latestEpisode'])
        ->toBeNull()
        ->and($data['pageMeta']->seo->title)
        ->toBe('Architecture Sessions — Page 2')
        ->and($data['pageMeta']->seo->description)
        ->toBe(
            'Conversations about maintainable Laravel applications. Page 2 of 2.',
        )
        ->and($data['pageMeta']->seo->url)
        ->toBe($canonicalUrl)
        ->and($data['pageMeta']->seo->canonical_url)
        ->toBe($canonicalUrl);
});

it('describes a podcast series and offsets its paginated episodes', function () {
    Schema::useFixedOrigin();
    $podcast = Podcast::factory()->create([
        'name' => 'Coffee Chat',
        'slug' => 'coffee-chat',
        'description' => 'A weekly chat.',
    ]);

    foreach (range(1, 21) as $day) {
        Episode::factory()
            ->for($podcast)
            ->published()
            ->create(['title' => "Episode {$day}", 'slug' => "episode-{$day}", 'published_at' => now()->subDays($day)]);
    }

    Paginator::currentPageResolver(fn (): int => 2);

    $data = app(PodcastShowViewModel::class)
        ->data($podcast);

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        [
            '@type' => 'PodcastSeries',
            '@id' => 'https://example.test/podcasts/coffee-chat#podcast',
            'name' => 'Coffee Chat',
            'url' => 'https://example.test/podcasts/coffee-chat',
            'author' => Schema::author(),
            'description' => 'A weekly chat.',
        ],
        ...Schema::collection('Coffee Chat Episodes', 'https://example.test/podcasts/coffee-chat?page=2', [
            21 => ['Episode 21', 'https://example.test/podcasts/coffee-chat/episode-21'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Podcast', 'https://example.test/podcasts'],
            ['Coffee Chat', 'https://example.test/podcasts/coffee-chat?page=2'],
        ]),
    ]);
});

it('rejects an out-of-range podcast page', function () {
    $podcast = Podcast::factory()->create([
        'name' => 'Architecture Sessions',
        'description' => 'Conversations about maintainable Laravel applications.',
    ]);

    Paginator::currentPageResolver(fn (): int => 2);

    expect(fn () => app(PodcastShowViewModel::class)
        ->data($podcast))
        ->toThrow(NotFoundHttpException::class);
});
